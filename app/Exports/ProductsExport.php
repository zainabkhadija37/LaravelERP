<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters = []) {}

    public function collection()
    {
        return Product::query()
            ->with(['category', 'supplier'])
            ->withSum('warehouses as total_stock', 'product_warehouse.quantity')
            ->search($this->filters['search'] ?? null)
            ->when(isset($this->filters['category_id']), fn ($q) => $q->where('category_id', $this->filters['category_id']))
            ->get();
    }

    public function headings(): array
    {
        return ['SKU', 'Name', 'Category', 'Supplier', 'Cost Price', 'Selling Price', 'Total Stock', 'Reorder Level', 'Active'];
    }

    public function map($product): array
    {
        return [
            $product->sku,
            $product->name,
            $product->category?->name,
            $product->supplier?->name,
            $product->cost_price,
            $product->selling_price,
            $product->total_stock ?? 0,
            $product->reorder_level,
            $product->is_active ? 'Yes' : 'No',
        ];
    }
}
