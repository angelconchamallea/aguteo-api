<?php

namespace Database\Seeders;

use App\Models\AgeStage;
use Illuminate\Database\Seeder;

class AgeStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => '0-3 meses', 'slug' => '0-3m', 'min_months' => 0,  'max_months' => 3,  'color_token' => 'aqua',      'tagline' => 'Recién llegado',     'sort_order' => 1],
            ['name' => '3-6 meses', 'slug' => '3-6m', 'min_months' => 3,  'max_months' => 6,  'color_token' => 'lavender',   'tagline' => 'Descubriendo todo',  'sort_order' => 2],
            ['name' => '6-12 meses','slug' => '6-12m','min_months' => 6,  'max_months' => 12, 'color_token' => 'tangerine',  'tagline' => 'Ya se mueve',        'sort_order' => 3],
            ['name' => '12-24 meses','slug'=>'12-24m','min_months' => 12, 'max_months' => 24, 'color_token' => 'rose',       'tagline' => 'Explorando el mundo','sort_order' => 4],
        ];

        foreach ($stages as $stage) {
            AgeStage::create($stage);
        }
    }
}
