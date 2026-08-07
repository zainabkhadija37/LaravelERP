<?php

namespace App\Models;

use App\Events\SaleCompleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'invoice_number', 'customer_id', 'warehouse_id', 'created_by',
        'status', 'payment_status', 'sale_date',
        'subtotal', 'discount_total', 'tax_total', 'grand_total', 'paid_amount', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'grand_total', 'paid_amount'])
            ->logOnlyDirty()
            ->useLogName('sale');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
        event(new SaleCompleted($this));
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearch($query, ?string $term)
    {
        return $term ? $query->where('invoice_number', 'ilike', "%{$term}%") : $query;
    }

    public function scopeBetweenDates($query, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to));
    }
}
