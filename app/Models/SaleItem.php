<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'quantity', 'unit_price', 'discount', 'tax_rate', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
