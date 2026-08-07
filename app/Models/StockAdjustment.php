<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockAdjustment extends Model
{
    use HasFactory, LogsActivity;

    public const TYPE_INCREASE = 'increase';

    public const TYPE_DECREASE = 'decrease';

    protected $fillable = [
        'product_id', 'warehouse_id', 'adjusted_by', 'type', 'reason',
        'quantity_before', 'quantity_change', 'quantity_after', 'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('stock_adjustment');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
