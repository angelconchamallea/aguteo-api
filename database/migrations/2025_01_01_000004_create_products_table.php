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
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description');
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_at_price')->nullable();
            $table->unsignedInteger('cost_price')->nullable();
            $table->boolean('has_variants')->default(false);
            $table->unsignedInteger('stock')->nullable();
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->enum('status', ['active', 'draft', 'out_of_stock'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('weight_grams')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
