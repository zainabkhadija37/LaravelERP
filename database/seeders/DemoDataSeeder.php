<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * Populates realistic-looking demo data so anyone who clones this repo
 * can `php artisan migrate --seed` and immediately see a working ERP
 * with products, stock levels, suppliers and customers — instead of
 * staring at empty tables.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::factory(3)->create();

        $categories = Category::factory(6)->create();

        $suppliers = Supplier::factory(8)->create();

        Customer::factory(20)->create();

        Product::factory(50)
            ->recycle($categories)
            ->recycle($suppliers)
            ->create()
            ->each(function (Product $product) use ($warehouses) {
                $product->warehouses()->attach(
                    $warehouses->mapWithKeys(fn ($w) => [$w->id => ['quantity' => fake()->numberBetween(0, 200)]])
                );
            });
    }
}
