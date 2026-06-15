<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL doesn't support simple enum→varchar change via Schema.
        // Use raw SQL to cast the column type.
        DB::statement('ALTER TABLE product_variants ALTER COLUMN size TYPE VARCHAR(50) USING size::VARCHAR');
    }

    public function down(): void
    {
        // Restore enum (only safe if data only contains original values)
        DB::statement("ALTER TABLE product_variants ALTER COLUMN size TYPE VARCHAR(20)");
    }
};
