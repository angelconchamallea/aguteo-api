<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('size', ['RN', '0-3m', '3-6m', '6-9m', '9-12m', '12-18m', '18-24m'])->nullable();
            $table->string('color')->nullable();
            $table->string('sku')->unique();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('price_override')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
