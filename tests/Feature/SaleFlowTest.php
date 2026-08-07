<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');

    $this->warehouse = Warehouse::factory()->create();
    $this->customer = Customer::factory()->create();
    $this->product = Product::factory()->create(['selling_price' => 100]);
    $this->product->warehouses()->attach($this->warehouse->id, ['quantity' => 50]);
});

it('creates a pending sale without touching stock', function () {
    $response = $this->actingAs($this->user)->postJson('/api/v1/sales', [
        'customer_id' => $this->customer->id,
        'warehouse_id' => $this->warehouse->id,
        'sale_date' => now()->toDateString(),
        'items' => [
            ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 100, 'tax_rate' => 10],
        ],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('product_warehouse', [
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50, // unchanged until completed
    ]);
});

it('deducts stock and fires SaleCompleted when a sale is completed', function () {
    Event::fake([\App\Events\SaleCompleted::class]);

    $sale = app(\App\Services\SaleService::class)->create([
        'customer_id' => $this->customer->id,
        'warehouse_id' => $this->warehouse->id,
        'sale_date' => now()->toDateString(),
        'items' => [
            ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 100, 'tax_rate' => 0],
        ],
    ], $this->user);

    $response = $this->actingAs($this->user)->postJson("/api/v1/sales/{$sale->id}/complete");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'completed');

    $this->assertDatabaseHas('product_warehouse', [
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 45,
    ]);

    Event::assertDispatched(\App\Events\SaleCompleted::class);
});

it('rejects a sale for a product with insufficient stock', function () {
    $sale = app(\App\Services\SaleService::class)->create([
        'customer_id' => $this->customer->id,
        'warehouse_id' => $this->warehouse->id,
        'sale_date' => now()->toDateString(),
        'items' => [
            ['product_id' => $this->product->id, 'quantity' => 999, 'unit_price' => 100, 'tax_rate' => 0],
        ],
    ], $this->user);

    $response = $this->actingAs($this->user)->postJson("/api/v1/sales/{$sale->id}/complete");

    $response->assertStatus(422);
});

it('prevents unauthenticated users from accessing sales', function () {
    $this->getJson('/api/v1/sales')->assertUnauthorized();
});

it('prevents an employee without permission from cancelling a purchase order', function () {
    $employee = User::factory()->create();
    $employee->assignRole('Employee');

    $po = \App\Models\PurchaseOrder::factory()->create(['status' => 'pending']);

    $this->actingAs($employee)
        ->postJson("/api/v1/purchase-orders/{$po->id}/cancel")
        ->assertForbidden();
});
