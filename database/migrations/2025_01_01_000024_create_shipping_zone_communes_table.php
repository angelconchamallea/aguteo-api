<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zone_communes', function (Blueprint $table) {
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commune_id')->constrained()->cascadeOnDelete();
            $table->primary(['shipping_zone_id', 'commune_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_communes');
    }
};
