<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id'     => Product::factory()->withVariants(),
            'size'           => $this->faker->randomElement(['RN', '0-3m', '3-6m', '6-9m', '9-12m', '12-18m', '18-24m']),
            'color'          => null,
            'sku'            => strtoupper(Str::random(3)) . '-' . $this->faker->numerify('####'),
            'stock'          => $this->faker->numberBetween(0, 50),
            'price_override' => null,
        ];
    }
}
