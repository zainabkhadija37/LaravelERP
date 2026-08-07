<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SaleService
{
    public function __construct(private readonly StockService $stockService) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['customer', 'warehouse', 'createdBy'])
            ->withCount('items')
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->betweenDates($filters['from'] ?? null, $filters['to'] ?? null)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a sale as "pending" without touching stock yet — stock is only
     * deducted once the sale is completed via complete(), mirroring how a
     * real POS/e-commerce checkout separates "order placed" from "fulfilled".
     *
     * @param  array{customer_id:int, warehouse_id:int, sale_date:string, notes:?string, items: array<int, array{product_id:int, quantity:int, unit_price:float, discount:float, tax_rate:float}>}  $data
     */
    public function create(array $data, User $createdBy): Sale
    {
        return DB::transaction(function () use ($data, $createdBy) {
            [$subtotal, $discountTotal, $taxTotal] = [0, 0, 0];

            foreach ($data['items'] as $item) {
                $lineGross = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $item['discount'] ?? 0;
                $lineTaxable = $lineGross - $lineDiscount;
                $lineTax = $lineTaxable * (($item['tax_rate'] ?? 0) / 100);

                $subtotal += $lineGross;
                $discountTotal += $lineDiscount;
                $taxTotal += $lineTax;
            }

            $sale = Sale::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $createdBy->id,
                'status' => Sale::STATUS_PENDING,
                'payment_status' => 'unpaid',
                'sale_date' => $data['sale_date'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $subtotal - $discountTotal + $taxTotal,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $lineGross = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $item['discount'] ?? 0;
                $lineTaxable = $lineGross - $lineDiscount;
                $lineTax = $lineTaxable * (($item['tax_rate'] ?? 0) / 100);

                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $lineDiscount,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'line_total' => $lineTaxable + $lineTax,
                ]);
            }

            return $sale->load('items.product', 'customer', 'warehouse');
        });
    }

    /**
     * Complete a pending sale: deduct stock for every line item and fire
     * the SaleCompleted event (queued listeners handle notification + activity log).
     */
    public function complete(Sale $sale): Sale
    {
        if ($sale->status !== Sale::STATUS_PENDING) {
            throw new RuntimeException('Only pending sales can be completed.');
        }

        return DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $this->stockService->decrease(
                    $item->product,
                    $sale->warehouse,
                    $item->quantity,
                    StockMovement::TYPE_SALE,
                    $sale
                );
            }

            $sale->markAsCompleted();

            return $sale->fresh('items.product');
        });
    }

    public function recordPayment(Sale $sale, float $amount): Sale
    {
        $newPaid = $sale->paid_amount + $amount;
        $status = $newPaid >= $sale->grand_total ? 'paid' : 'partial';

        $sale->update(['paid_amount' => $newPaid, 'payment_status' => $status]);

        return $sale->fresh();
    }

    public function cancel(Sale $sale): Sale
    {
        if ($sale->status === Sale::STATUS_COMPLETED) {
            throw new RuntimeException('A completed sale cannot be cancelled directly; issue a refund instead.');
        }

        $sale->update(['status' => Sale::STATUS_CANCELLED]);

        return $sale->fresh();
    }

    private function generateInvoiceNumber(): string
    {
        return 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
    }
}
