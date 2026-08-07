<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function __construct(private readonly StockService $stockService) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return StockAdjustment::query()
            ->with(['product', 'warehouse', 'adjustedBy'])
            ->when(isset($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when(isset($filters['warehouse_id']), fn ($q) => $q->where('warehouse_id', $filters['warehouse_id']))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function adjust(
        Product $product,
        Warehouse $warehouse,
        User $adjustedBy,
        string $type,
        string $reason,
        int $quantity,
        ?string $notes = null
    ): StockAdjustment {
        return DB::transaction(function () use ($product, $warehouse, $adjustedBy, $type, $reason, $quantity, $notes) {
            $before = $this->stockService->currentQuantity($product, $warehouse);

            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'adjusted_by' => $adjustedBy->id,
                'type' => $type,
                'reason' => $reason,
                'quantity_before' => $before,
                'quantity_change' => $quantity,
                'quantity_after' => $type === StockAdjustment::TYPE_INCREASE ? $before + $quantity : $before - $quantity,
                'notes' => $notes,
            ]);

            $movementType = $type === StockAdjustment::TYPE_INCREASE
                ? StockMovement::TYPE_ADJUSTMENT_INCREASE
                : StockMovement::TYPE_ADJUSTMENT_DECREASE;

            if ($type === StockAdjustment::TYPE_INCREASE) {
                $this->stockService->increase($product, $warehouse, $quantity, $movementType, $adjustment);
            } else {
                $this->stockService->decrease($product, $warehouse, $quantity, $movementType, $adjustment);
            }

            return $adjustment->fresh(['product', 'warehouse', 'adjustedBy']);
        });
    }
}
