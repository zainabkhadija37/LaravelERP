<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 5, 500);

        return [
            'name' => ucwords(fake()->words(3, true)),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-#####??')),
            'barcode' => fake()->unique()->ean13(),
            'description' => fake()->sentence(),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'cost_price' => $cost,
            'selling_price' => round($cost * fake()->randomFloat(2, 1.15, 1.8), 2),
            'unit' => fake()->randomElement(['pcs', 'box', 'kg', 'ltr']),
            'reorder_level' => fake()->numberBetween(5, 30),
            'is_active' => true,
        ];
    }
}
