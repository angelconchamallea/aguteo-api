<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE product_variants DROP CONSTRAINT IF EXISTS product_variants_size_check');
    }

    public function down(): void
    {
        // Cannot restore the enum check without knowing the original values at rollback time.
    }
};
