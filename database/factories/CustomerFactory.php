<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'     => $this->faker->name(),
            'email'    => $this->faker->unique()->safeEmail(),
            'phone'    => '+569' . $this->faker->numerify('########'),
            'rut'      => null,
            'password' => null,
        ];
    }
}
