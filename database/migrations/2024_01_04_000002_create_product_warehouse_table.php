<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot table holding per-warehouse stock quantity for each product.
 * This is the single source of truth for "how many units of Product X
 * are sitting in Warehouse Y right now".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouse');
    }
};
