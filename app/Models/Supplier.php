<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name', 'company_name', 'email', 'phone', 'address', 'tax_number', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('supplier');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        return $term
            ? $query->where(fn ($q) => $q->where('name', 'ilike', "%{$term}%")
                ->orWhere('company_name', 'ilike', "%{$term}%")
                ->orWhere('email', 'ilike', "%{$term}%"))
            : $query;
    }
}
