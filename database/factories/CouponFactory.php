<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'                      => strtoupper($this->faker->unique()->bothify('??###')),
            'type'                      => 'percentage',
            'value'                     => $this->faker->numberBetween(5, 30),
            'min_purchase_amount'       => null,
            'usage_limit'               => null,
            'usage_limit_per_customer'  => null,
            'times_used'                => 0,
            'starts_at'                 => null,
            'expires_at'                => null,
            'is_active'                 => true,
        ];
    }

    public function percentage(int $value = 10): static
    {
        return $this->state(['type' => 'percentage', 'value' => $value]);
    }

    public function fixedAmount(int $value): static
    {
        return $this->state(['type' => 'fixed_amount', 'value' => $value]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }

    public function notYetActive(): static
    {
        return $this->state(['starts_at' => now()->addDay()]);
    }

    public function withMinPurchase(int $amount): static
    {
        return $this->state(['min_purchase_amount' => $amount]);
    }

    public function exhausted(): static
    {
        return $this->state(['usage_limit' => 5, 'times_used' => 5]);
    }
}
