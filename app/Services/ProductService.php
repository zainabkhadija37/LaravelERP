<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'supplier'])
            ->withSum('warehouses as total_stock', 'product_warehouse.quantity')
            ->search($filters['search'] ?? null)
            ->when(isset($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['low_stock']) && $filters['low_stock'], fn ($q) => $q->lowStock())
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);

            // Initialise a zero-quantity row per active warehouse so stock
            // reports never have to deal with "missing" rows.
            $warehouseIds = \App\Models\Warehouse::active()->pluck('id');
            $product->warehouses()->attach(
                $warehouseIds->mapWithKeys(fn ($id) => [$id => ['quantity' => 0]])
            );

            return $product->load('category', 'supplier', 'warehouses');
        });
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh(['category', 'supplier']);
    }

    public function delete(Product $product): void
    {
        $product->delete(); // soft delete
    }

    public function restore(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return $product;
    }
}
