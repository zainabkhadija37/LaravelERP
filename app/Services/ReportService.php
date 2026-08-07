<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Read-only reporting queries used by the Reports screens and the
 * CSV/PDF export jobs. Kept separate from DashboardService because
 * reports are typically parameterised (date ranges, filters) and
 * exported, whereas the dashboard is a fixed, cached snapshot.
 */
class ReportService
{
    public function salesReport(?string $from = null, ?string $to = null): \Illuminate\Support\Collection
    {
        return Sale::query()
            ->with(['customer', 'warehouse'])
            ->status(Sale::STATUS_COMPLETED)
            ->betweenDates($from, $to)
            ->orderBy('sale_date')
            ->get();
    }

    public function inventoryValuationReport(): \Illuminate\Support\Collection
    {
        return Product::query()
            ->with('category')
            ->withSum('warehouses as total_stock', 'product_warehouse.quantity')
            ->active()
            ->get()
            ->map(function (Product $product) {
                $product->stock_value = round(($product->total_stock ?? 0) * $product->cost_price, 2);

                return $product;
            });
    }

    public function stockMovementReport(?int $productId = null, ?int $warehouseId = null): \Illuminate\Support\Collection
    {
        return StockMovement::query()
            ->with(['product', 'warehouse'])
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->latest()
            ->limit(500)
            ->get();
    }

    public function lowStockReport(): \Illuminate\Support\Collection
    {
        return Product::query()
            ->with(['category', 'supplier'])
            ->withSum('warehouses as total_stock', 'product_warehouse.quantity')
            ->active()
            ->lowStock()
            ->get();
    }

    public function purchaseOrdersReport(?string $from = null, ?string $to = null): \Illuminate\Support\Collection
    {
        return DB::table('purchase_orders')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->selectRaw('purchase_orders.*, suppliers.name as supplier_name')
            ->when($from, fn ($q) => $q->whereDate('order_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('order_date', '<=', $to))
            ->orderByDesc('order_date')
            ->get();
    }
}
