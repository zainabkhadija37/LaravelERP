<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

it('lists paginated products for an authenticated user', function () {
    Product::factory(20)->create();

    $response = $this->actingAs($this->admin)->getJson('/api/v1/products?per_page=10');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(10);
    expect($response->json('meta.total'))->toBe(20);
});

it('filters products by search term', function () {
    Product::factory()->create(['name' => 'Wireless Mouse', 'sku' => 'MOUSE-001']);
    Product::factory()->create(['name' => 'Mechanical Keyboard', 'sku' => 'KEY-001']);

    $response = $this->actingAs($this->admin)->getJson('/api/v1/products?search=Mouse');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Wireless Mouse');
});

it('validates that selling price cannot be created below cost price', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->admin)->postJson('/api/v1/products', [
        'name' => 'Test Product',
        'sku' => 'TP-001',
        'category_id' => $category->id,
        'cost_price' => 100,
        'selling_price' => 50,
        'unit' => 'pcs',
        'reorder_level' => 5,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('selling_price');
});

it('soft deletes a product instead of removing it permanently', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)->deleteJson("/api/v1/products/{$product->id}")
        ->assertNoContent();

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});
