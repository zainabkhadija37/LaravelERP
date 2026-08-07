<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PurchaseOrderService
{
    public function __construct(private readonly StockService $stockService) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return PurchaseOrder::query()
            ->with(['supplier', 'warehouse', 'createdBy'])
            ->withCount('items')
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{supplier_id:int, warehouse_id:int, order_date:string, expected_date:?string, notes:?string, items: array<int, array{product_id:int, quantity_ordered:int, unit_cost:float, tax_rate:float}>}  $data
     */
    public function create(array $data, User $createdBy): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $subtotal = 0;
            $taxTotal = 0;

            foreach ($data['items'] as $item) {
                $lineSubtotal = $item['quantity_ordered'] * $item['unit_cost'];
                $lineTax = $lineSubtotal * ($item['tax_rate'] / 100);
                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;
            }

            $po = PurchaseOrder::create([
                'po_number' => $this->generatePoNumber(),
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $createdBy->id,
                'status' => PurchaseOrder::STATUS_PENDING,
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $subtotal + $taxTotal,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $lineSubtotal = $item['quantity_ordered'] * $item['unit_cost'];
                $lineTax = $lineSubtotal * ($item['tax_rate'] / 100);

                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'tax_rate' => $item['tax_rate'],
                    'line_total' => $lineSubtotal + $lineTax,
                ]);
            }

            return $po->load('items.product', 'supplier', 'warehouse');
        });
    }

    public function approve(PurchaseOrder $po, User $approver): PurchaseOrder
    {
        if ($po->status !== PurchaseOrder::STATUS_PENDING) {
            throw new RuntimeException('Only pending purchase orders can be approved.');
        }

        $po->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => $approver->id,
        ]);

        return $po->fresh();
    }

    /**
     * Mark a PO as received and push the ordered quantities into stock.
     * This is the point where StockService actually increments warehouse quantity.
     */
    public function receive(PurchaseOrder $po): PurchaseOrder
    {
        if ($po->status !== PurchaseOrder::STATUS_APPROVED) {
            throw new RuntimeException('Only approved purchase orders can be received.');
        }

        return DB::transaction(function () use ($po) {
            $warehouse = $po->warehouse;

            foreach ($po->items as $item) {
                $this->stockService->increase(
                    $item->product,
                    $warehouse,
                    $item->quantity_ordered,
                    \App\Models\StockMovement::TYPE_PURCHASE,
                    $po
                );

                $item->update(['quantity_received' => $item->quantity_ordered]);
            }

            $po->markAsReceived();

            return $po->fresh('items.product');
        });
    }

    public function cancel(PurchaseOrder $po): PurchaseOrder
    {
        if (in_array($po->status, [PurchaseOrder::STATUS_RECEIVED, PurchaseOrder::STATUS_CANCELLED])) {
            throw new RuntimeException('This purchase order can no longer be cancelled.');
        }

        $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        return $po->fresh();
    }

    private function generatePoNumber(): string
    {
        return 'PO-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
    }
}
