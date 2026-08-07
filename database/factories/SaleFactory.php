<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = \App\Models\Sale::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.strtoupper(fake()->bothify('#####')),
            'customer_id' => Customer::factory(),
            'warehouse_id' => Warehouse::factory(),
            'created_by' => User::factory(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'sale_date' => now(),
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'grand_total' => 0,
            'paid_amount' => 0,
        ];
    }
}
