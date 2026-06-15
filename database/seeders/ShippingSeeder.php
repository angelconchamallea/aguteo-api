<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        $rm = ShippingZone::create(['name' => 'Región Metropolitana', 'is_active' => true]);
        ShippingRate::create([
            'shipping_zone_id' => $rm->id,
            'name'             => 'Despacho estándar RM',
            'price'            => 3500,
            'free_from_amount' => 30000,
            'estimated_days'   => '2-4 días hábiles',
        ]);
        ShippingRate::create([
            'shipping_zone_id' => $rm->id,
            'name'             => 'Despacho express RM',
            'price'            => 6990,
            'free_from_amount' => null,
            'estimated_days'   => '1-2 días hábiles',
        ]);

        $rmCommuneIds = Commune::whereHas('region', fn ($q) => $q->where('code', 'RM'))
            ->pluck('id');
        $rm->communes()->attach($rmCommuneIds);

        $nacional = ShippingZone::create(['name' => 'Resto de Chile', 'is_active' => true]);
        ShippingRate::create([
            'shipping_zone_id' => $nacional->id,
            'name'             => 'Despacho a regiones',
            'price'            => 5990,
            'free_from_amount' => 50000,
            'estimated_days'   => '4-7 días hábiles',
        ]);
    }
}
