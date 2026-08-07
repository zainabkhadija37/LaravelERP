<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates the numbers shown on the main dashboard. Results are cached
 * briefly since these queries run aggregate functions across large tables
 * and the dashboard is typically the most-viewed page in the app.
 */
class DashboardService
{
    public function summary(): array
    {
        return Cache::remember('dashboard:summary', now()->addMinutes(5), function () {
            return [
                'total_products' => Product::active()->count(),
                'low_stock_products' => Product::active()->lowStock()->count(),
                'total_customers' => \App\Models\Customer::active()->count(),
                'total_suppliers' => \App\Models\Supplier::active()->count(),
                'pending_purchase_orders' => PurchaseOrder::status(PurchaseOrder::STATUS_PENDING)->count(),
                'sales_today' => Sale::whereDate('sale_date', today())->status(Sale::STATUS_COMPLETED)->sum('grand_total'),
                'sales_this_month' => Sale::whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->status(Sale::STATUS_COMPLETED)->sum('grand_total'),
                'revenue_last_7_days' => $this->revenueLastNDays(7),
            ];
        });
    }

    public function revenueLastNDays(int $days): array
    {
        $rows = Sale::query()
            ->selectRaw('sale_date, SUM(grand_total) as total')
            ->status(Sale::STATUS_COMPLETED)
            ->where('sale_date', '>=', now()->subDays($days - 1)->toDateString())
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy(fn ($row) => $row->sale_date->toDateString());

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $series[$date] = (float) ($rows[$date]->total ?? 0);
        }

        return $series;
    }

    public function topSellingProducts(int $limit = 5): \Illuminate\Support\Collection
    {
        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->selectRaw('products.id, products.name, SUM(sale_items.quantity) as units_sold, SUM(sale_items.line_total) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get();
    }
}
