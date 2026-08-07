<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_SALE = 'sale';

    public const TYPE_ADJUSTMENT_INCREASE = 'adjustment_increase';

    public const TYPE_ADJUSTMENT_DECREASE = 'adjustment_decrease';

    protected $fillable = [
        'product_id', 'warehouse_id', 'type', 'reference_type', 'reference_id', 'quantity', 'balance_after',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
