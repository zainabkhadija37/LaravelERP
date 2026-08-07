<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');

    $this->warehouse = Warehouse::factory()->create();
    $this->supplier = Supplier::factory()->create();
    $this->product = Product::factory()->create();
    $this->product->warehouses()->attach($this->warehouse->id, ['quantity' => 0]);
});

it('creates, approves and receives a purchase order, increasing stock', function () {
    $create = $this->actingAs($this->admin)->postJson('/api/v1/purchase-orders', [
        'supplier_id' => $this->supplier->id,
        'warehouse_id' => $this->warehouse->id,
        'order_date' => now()->toDateString(),
        'items' => [
            ['product_id' => $this->product->id, 'quantity_ordered' => 40, 'unit_cost' => 12.50, 'tax_rate' => 5],
        ],
    ]);

    $create->assertCreated();
    $poId = $create->json('data.id');

    $this->actingAs($this->admin)->postJson("/api/v1/purchase-orders/{$poId}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'approved');

    $receive = $this->actingAs($this->admin)->postJson("/api/v1/purchase-orders/{$poId}/receive");

    $receive->assertOk();
    $receive->assertJsonPath('data.status', 'received');

    $this->assertDatabaseHas('product_warehouse', [
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 40,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $this->product->id,
        'type' => 'purchase',
        'quantity' => 40,
    ]);
});

it('rejects receiving a purchase order that has not been approved', function () {
    $create = $this->actingAs($this->admin)->postJson('/api/v1/purchase-orders', [
        'supplier_id' => $this->supplier->id,
        'warehouse_id' => $this->warehouse->id,
        'order_date' => now()->toDateString(),
        'items' => [
            ['product_id' => $this->product->id, 'quantity_ordered' => 10, 'unit_cost' => 5, 'tax_rate' => 0],
        ],
    ]);

    $poId = $create->json('data.id');

    $this->actingAs($this->admin)->postJson("/api/v1/purchase-orders/{$poId}/receive")
        ->assertStatus(422);
});
