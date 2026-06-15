<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $price = $this->faker->numberBetween(2990, 49990);

        return [
            'sku'               => strtoupper(Str::random(3)) . '-AGB-' . strtoupper(Str::random(4)) . '-' . $this->faker->numerify('###'),
            'name'              => ucfirst($name),
            'slug'              => Str::slug($name) . '-' . $this->faker->numerify('###'),
            'short_description' => $this->faker->sentence(),
            'description'       => $this->faker->paragraphs(2, true),
            'brand_id'          => Brand::factory(),
            'category_id'       => Category::factory(),
            'price'             => $price,
            'compare_at_price'  => $this->faker->boolean(30) ? (int) ($price * 1.2) : null,
            'cost_price'        => (int) ($price * 0.4),
            'has_variants'      => false,
            'stock'             => $this->faker->numberBetween(0, 100),
            'low_stock_threshold' => 5,
            'status'            => $this->faker->randomElement(['active', 'draft']),
            'featured'          => $this->faker->boolean(20),
            'weight_grams'      => $this->faker->numberBetween(50, 1000),
        ];
    }

    public function withVariants(): static
    {
        return $this->state(['has_variants' => true, 'stock' => null]);
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }
}
