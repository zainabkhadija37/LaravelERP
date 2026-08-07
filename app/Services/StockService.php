<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Single source of truth for mutating stock quantities.
 *
 * Every other service (PurchaseOrderService, SaleService, StockAdjustmentService)
 * delegates its actual quantity math to this class so there is exactly one
 * code path that touches `product_warehouse.quantity` and writes to the
 * `stock_movements` ledger. This avoids drift between "what we think is in
 * stock" and "what the movement ledger says happened".
 */
class StockService
{
    /**
     * Increase stock for a product in a warehouse (e.g. purchase order received).
     */
    public function increase(Product $product, Warehouse $warehouse, int $quantity, string $type, $reference): int
    {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $type, $reference) {
            $pivot = $this->lockPivotRow($product, $warehouse);

            $newQuantity = $pivot['quantity'] + $quantity;

            $product->warehouses()->syncWithoutDetaching([
                $warehouse->id => ['quantity' => $newQuantity],
            ]);

            $this->recordMovement($product, $warehouse, $type, $reference, $quantity, $newQuantity);

            return $newQuantity;
        });
    }

    /**
     * Decrease stock for a product in a warehouse (e.g. sale completed).
     *
     * @throws RuntimeException when there isn't enough stock to fulfil the request.
     */
    public function decrease(Product $product, Warehouse $warehouse, int $quantity, string $type, $reference): int
    {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $type, $reference) {
            $pivot = $this->lockPivotRow($product, $warehouse);

            if ($pivot['quantity'] < $quantity) {
                throw new RuntimeException(
                    "Insufficient stock for {$product->name} in {$warehouse->name}: ".
                    "requested {$quantity}, available {$pivot['quantity']}."
                );
            }

            $newQuantity = $pivot['quantity'] - $quantity;

            $product->warehouses()->syncWithoutDetaching([
                $warehouse->id => ['quantity' => $newQuantity],
            ]);

            $this->recordMovement($product, $warehouse, $type, $reference, -$quantity, $newQuantity);

            if ($newQuantity <= $product->reorder_level) {
                event(new \App\Events\StockLevelLow($product, $warehouse, $newQuantity));
            }

            return $newQuantity;
        });
    }

    public function currentQuantity(Product $product, Warehouse $warehouse): int
    {
        return (int) ($product->warehouses()
            ->where('warehouse_id', $warehouse->id)
            ->first()?->pivot->quantity ?? 0);
    }

    private function lockPivotRow(Product $product, Warehouse $warehouse): array
    {
        $row = DB::table('product_warehouse')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->lockForUpdate()
            ->first();

        if (! $row) {
            DB::table('product_warehouse')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['quantity' => 0];
        }

        return (array) $row;
    }

    private function recordMovement(Product $product, Warehouse $warehouse, string $type, $reference, int $signedQuantity, int $balanceAfter): void
    {
        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => $type,
            'reference_type' => get_class($reference),
            'reference_id' => $reference->id,
            'quantity' => $signedQuantity,
            'balance_after' => $balanceAfter,
        ]);
    }
}
