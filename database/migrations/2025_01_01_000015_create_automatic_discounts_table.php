<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automatic_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['percentage', 'fixed_amount']);
            $table->unsignedInteger('value');
            $table->jsonb('conditions')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automatic_discounts');
    }
};
