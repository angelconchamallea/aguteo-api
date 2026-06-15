<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Aguteo Babys', 'slug' => 'aguteo-babys', 'description' => 'Nuestra marca propia, hecha con amor por papás de gemelos.'],
            ['name' => 'Amma', 'slug' => 'amma', 'description' => 'Ropa infantil de algodón suave para bebés y niños.'],
            ['name' => 'Chicco', 'slug' => 'chicco', 'description' => 'Marca italiana de productos para bebés.'],
            ['name' => 'Huggies', 'slug' => 'huggies', 'description' => 'Pañales y cuidado para bebés.'],
            ['name' => 'Medela', 'slug' => 'medela', 'description' => 'Soluciones de lactancia materna.'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
