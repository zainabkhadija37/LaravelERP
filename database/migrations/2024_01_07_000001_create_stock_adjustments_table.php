<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('adjusted_by')->constrained('users')->restrictOnDelete();
            $table->string('type'); // increase, decrease
            $table->string('reason'); // damaged, lost, found, correction, other
            $table->integer('quantity_before');
            $table->integer('quantity_change');
            $table->integer('quantity_after');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
