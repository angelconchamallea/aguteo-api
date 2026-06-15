<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BrandSeeder::class,
            CategorySeeder::class,
            AgeStageSeeder::class,
            RegionSeeder::class,
            CommuneSeeder::class,
            ShippingSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
