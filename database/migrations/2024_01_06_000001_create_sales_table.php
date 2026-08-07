<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('pending'); // pending, completed, cancelled, refunded
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            $table->date('sale_date');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['invoice_number']);
            $table->index(['sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
