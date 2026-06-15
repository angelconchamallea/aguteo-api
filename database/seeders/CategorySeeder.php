<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name' => 'Ropa Bebé e Infantil', 'slug' => 'ropa-bebe', 'color_token' => 'blush',
                'icon' => 'shirt', 'sort_order' => 1,
                'children' => [
                    ['name' => 'Conjuntos',      'slug' => 'conjuntos',      'sort_order' => 1],
                    ['name' => 'Ropa interior',  'slug' => 'ropa-interior',  'sort_order' => 2],
                    ['name' => 'Pijamas',        'slug' => 'pijamas',        'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Alimentación', 'slug' => 'alimentacion', 'color_token' => 'butter',
                'icon' => 'baby-bottle', 'sort_order' => 2,
                'children' => [
                    ['name' => 'Compotas',                    'slug' => 'compotas',                    'sort_order' => 1],
                    ['name' => 'Jugos',                       'slug' => 'jugos',                       'sort_order' => 2],
                    ['name' => 'Accesorios de Alimentación',  'slug' => 'accesorios-alimentacion',     'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Cuidado e Higiene', 'slug' => 'cuidado-higiene', 'color_token' => 'aqua',
                'icon' => 'sparkles', 'sort_order' => 3,
                'children' => [
                    ['name' => 'Pañales',           'slug' => 'panales',           'sort_order' => 1],
                    ['name' => 'Toallitas Húmedas', 'slug' => 'toallitas-humedas', 'sort_order' => 2],
                    ['name' => 'Cremas y Pomadas',  'slug' => 'cremas-pomadas',    'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Descanso y Baño', 'slug' => 'descanso-bano', 'color_token' => 'lavender',
                'icon' => 'moon', 'sort_order' => 4,
                'children' => [
                    ['name' => 'Tutos y Pañales de Género', 'slug' => 'tutos-panales-genero', 'sort_order' => 1],
                    ['name' => 'Mantas y Frazadas',         'slug' => 'mantas-frazadas',       'sort_order' => 2],
                    ['name' => 'Toallas de Baño',           'slug' => 'toallas-bano',          'sort_order' => 3],
                    ['name' => 'Batas de Baño',             'slug' => 'batas-bano',            'sort_order' => 4],
                    ['name' => 'Accesorios de Baño',        'slug' => 'accesorios-bano',       'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Juguetes', 'slug' => 'juguetes', 'color_token' => 'tangerine',
                'icon' => 'puzzle', 'sort_order' => 5,
                'children' => [
                    ['name' => 'Mordedores',            'slug' => 'mordedores',           'sort_order' => 1],
                    ['name' => 'Juguetes Sensoriales',  'slug' => 'juguetes-sensoriales', 'sort_order' => 2],
                    ['name' => 'Juguetes Didácticos',   'slug' => 'juguetes-didacticos',  'sort_order' => 3],
                    ['name' => 'Peluches y Sonajeros',  'slug' => 'peluches-sonajeros',   'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Mamá', 'slug' => 'mama', 'color_token' => 'sky',
                'icon' => 'heart', 'sort_order' => 6,
                'children' => [
                    ['name' => 'Ropa Maternal',          'slug' => 'ropa-maternal',          'sort_order' => 1],
                    ['name' => 'Ropa Interior Maternal', 'slug' => 'ropa-interior-maternal', 'sort_order' => 2],
                    ['name' => 'Lactancia',              'slug' => 'lactancia',              'sort_order' => 3],
                    ['name' => 'Bolsos Maternales',      'slug' => 'bolsos-maternales',      'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Packs y Regalos', 'slug' => 'packs-regalos', 'color_token' => 'coral',
                'icon' => 'gift', 'sort_order' => 7,
                'children' => [
                    ['name' => 'Pack Recién Nacido',  'slug' => 'pack-recien-nacido', 'sort_order' => 1],
                    ['name' => 'Pack Alimentación',   'slug' => 'pack-alimentacion',  'sort_order' => 2],
                    ['name' => 'Pack Juguetes',       'slug' => 'pack-juguetes',      'sort_order' => 3],
                    ['name' => 'Pack Mamá',           'slug' => 'pack-mama',          'sort_order' => 4],
                ],
            ],
        ];

        foreach ($tree as $rootData) {
            $children = $rootData['children'] ?? [];
            unset($rootData['children']);

            $root = Category::create(array_merge($rootData, ['depth' => 0, 'path' => '0']));
            $root->update(['path' => (string) $root->id]);

            foreach ($children as $childData) {
                $child = Category::create(array_merge($childData, [
                    'parent_id' => $root->id,
                    'depth' => 1,
                    'path' => '0',
                ]));
                $child->update(['path' => "{$root->id}/{$child->id}"]);
            }
        }
    }
}
