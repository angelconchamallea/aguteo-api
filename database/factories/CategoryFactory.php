<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'parent_id'   => null,
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name) . '-' . $this->faker->numerify('##'),
            'color_token' => $this->faker->randomElement(['blush', 'butter', 'aqua', 'lavender', 'tangerine', 'sky', 'coral']),
            'sort_order'  => 0,
            'depth'       => 0,
            'path'        => '0',
            'is_active'   => true,
        ];
    }
}
