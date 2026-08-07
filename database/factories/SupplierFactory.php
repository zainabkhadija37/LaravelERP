<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = \App\Models\Supplier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company_name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'tax_number' => fake()->numerify('TAX-########'),
            'is_active' => true,
        ];
    }
}
