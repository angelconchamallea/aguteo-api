<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'Arica y Parinacota',                              'code' => 'XV',  'sort_order' => 1],
            ['name' => 'Tarapacá',                                        'code' => 'I',   'sort_order' => 2],
            ['name' => 'Antofagasta',                                     'code' => 'II',  'sort_order' => 3],
            ['name' => 'Atacama',                                         'code' => 'III', 'sort_order' => 4],
            ['name' => 'Coquimbo',                                        'code' => 'IV',  'sort_order' => 5],
            ['name' => 'Valparaíso',                                      'code' => 'V',   'sort_order' => 6],
            ['name' => 'Metropolitana de Santiago',                       'code' => 'RM',  'sort_order' => 7],
            ['name' => 'Libertador General Bernardo O\'Higgins',          'code' => 'VI',  'sort_order' => 8],
            ['name' => 'Maule',                                           'code' => 'VII', 'sort_order' => 9],
            ['name' => 'Ñuble',                                           'code' => 'XVI', 'sort_order' => 10],
            ['name' => 'Biobío',                                          'code' => 'VIII','sort_order' => 11],
            ['name' => 'La Araucanía',                                    'code' => 'IX',  'sort_order' => 12],
            ['name' => 'Los Ríos',                                        'code' => 'XIV', 'sort_order' => 13],
            ['name' => 'Los Lagos',                                       'code' => 'X',   'sort_order' => 14],
            ['name' => 'Aysén del Gral. Carlos Ibáñez del Campo',        'code' => 'XI',  'sort_order' => 15],
            ['name' => 'Magallanes y de la Antártica Chilena',           'code' => 'XII', 'sort_order' => 16],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }
    }
}
