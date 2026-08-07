<?php

namespace App\Models;

use App\Events\PurchaseOrderReceived;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'po_number', 'supplier_id', 'warehouse_id', 'created_by', 'approved_by',
        'status', 'order_date', 'expected_date', 'received_date',
        'subtotal', 'tax_total', 'grand_total', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'received_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'grand_total', 'received_date'])
            ->logOnlyDirty()
            ->useLogName('purchase_order');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function markAsReceived(): void
    {
        $this->update(['status' => self::STATUS_RECEIVED, 'received_date' => now()]);
        event(new PurchaseOrderReceived($this));
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearch($query, ?string $term)
    {
        return $term ? $query->where('po_number', 'ilike', "%{$term}%") : $query;
    }
}
