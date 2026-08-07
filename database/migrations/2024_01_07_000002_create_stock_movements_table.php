<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable ledger of every stock change in the system, regardless of
 * source (purchase order received, sale completed, manual adjustment).
 * This lets ReportService rebuild stock history / valuation at any point in time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('type'); // purchase, sale, adjustment_increase, adjustment_decrease
            $table->morphs('reference'); // polymorphic: PurchaseOrder, Sale, StockAdjustment
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
