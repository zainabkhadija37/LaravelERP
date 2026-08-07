<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = \App\Models\Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->city().' Warehouse',
            'code' => strtoupper(fake()->unique()->lexify('WH-???')),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }
}
