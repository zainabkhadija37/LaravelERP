<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->string('unit')->default('pcs');
            $table->integer('reorder_level')->default(10);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name']);
            $table->index(['sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
