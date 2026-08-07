<?php

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\StockService;

beforeEach(function () {
    $this->stockService = app(StockService::class);
    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create();
    $this->product->warehouses()->attach($this->warehouse->id, ['quantity' => 0]);
});

it('increases stock and records a stock movement', function () {
    $po = PurchaseOrder::factory()->create();

    $newQuantity = $this->stockService->increase($this->product, $this->warehouse, 50, 'purchase', $po);

    expect($newQuantity)->toBe(50);

    $this->assertDatabaseHas('product_warehouse', [
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity' => 50,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'type' => 'purchase',
        'quantity' => 50,
        'balance_after' => 50,
    ]);
});

it('decreases stock when enough is available', function () {
    $this->stockService->increase($this->product, $this->warehouse, 100, 'purchase', PurchaseOrder::factory()->create());

    $newQuantity = $this->stockService->decrease(
        $this->product, $this->warehouse, 30, 'sale', \App\Models\Sale::factory()->create()
    );

    expect($newQuantity)->toBe(70);
});

it('throws when decreasing more stock than is available', function () {
    $this->stockService->increase($this->product, $this->warehouse, 10, 'purchase', PurchaseOrder::factory()->create());

    $this->stockService->decrease(
        $this->product, $this->warehouse, 999, 'sale', \App\Models\Sale::factory()->create()
    );
})->throws(RuntimeException::class);

it('fires a StockLevelLow event once stock drops to or below reorder level', function () {
    \Illuminate\Support\Facades\Event::fake([\App\Events\StockLevelLow::class]);

    $this->product->update(['reorder_level' => 20]);
    $this->stockService->increase($this->product, $this->warehouse, 25, 'purchase', PurchaseOrder::factory()->create());

    $this->stockService->decrease(
        $this->product, $this->warehouse, 10, 'sale', \App\Models\Sale::factory()->create()
    );

    \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\StockLevelLow::class);
});
