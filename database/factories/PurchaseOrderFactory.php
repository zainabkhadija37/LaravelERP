<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = \App\Models\PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number' => 'PO-'.now()->format('Ymd').'-'.strtoupper(fake()->bothify('#####')),
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'created_by' => User::factory(),
            'status' => 'draft',
            'order_date' => now(),
            'subtotal' => 0,
            'tax_total' => 0,
            'grand_total' => 0,
        ];
    }
}
