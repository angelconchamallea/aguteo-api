<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AgeStageFactory extends Factory
{
    public function definition(): array
    {
        $min  = $this->faker->numberBetween(0, 18);
        $max  = $min + 3;
        $slug = "{$min}-{$max}m";

        return [
            'name'        => "{$min}-{$max} meses",
            'slug'        => $slug . '-' . $this->faker->numerify('##'),
            'min_months'  => $min,
            'max_months'  => $max,
            'color_token' => $this->faker->hexColor(),
            'tagline'     => $this->faker->sentence(3),
            'sort_order'  => $min,
        ];
    }
}
