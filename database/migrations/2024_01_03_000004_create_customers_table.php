<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
