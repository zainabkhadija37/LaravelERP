<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name', 'sku', 'barcode', 'description', 'category_id', 'supplier_id',
        'cost_price', 'selling_price', 'unit', 'reorder_level', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'cost_price', 'selling_price', 'reorder_level', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('product');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Total quantity across all warehouses. Loads pivot data
     * so callers should eager load `warehouses` to avoid N+1.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->warehouses->sum(fn ($w) => $w->pivot->quantity);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        return $term
            ? $query->where(fn ($q) => $q->where('name', 'ilike', "%{$term}%")
                ->orWhere('sku', 'ilike', "%{$term}%")
                ->orWhere('barcode', 'ilike', "%{$term}%"))
            : $query;
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('warehouses', function ($q) {
            //
        })->whereRaw(
            '(select coalesce(sum(pw.quantity), 0) from product_warehouse pw where pw.product_id = products.id) <= products.reorder_level'
        );
    }
}
