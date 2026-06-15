<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    private static int $sequence = 1;

    public function definition(): array
    {
        $subtotal  = $this->faker->numberBetween(5000, 80000);
        $shipping  = $this->faker->randomElement([3500, 5990, 0]);
        $total     = $subtotal + $shipping;

        return [
            'order_number'    => 'AGB-' . str_pad(static::$sequence++, 6, '0', STR_PAD_LEFT),
            'customer_id'     => Customer::factory(),
            'status'          => $this->faker->randomElement(['pending', 'paid', 'preparing', 'shipped']),
            'subtotal'        => $subtotal,
            'discount_total'  => 0,
            'shipping_total'  => $shipping,
            'total'           => $total,
            'coupon_id'       => null,
            'coupon_code'     => null,
            'payment_method'  => 'webpay',
            'webpay_token'    => null,
            'webpay_buy_order'=> null,
            'webpay_authorization_code' => null,
            'webpay_card_last4'         => null,
            'shipping_address' => [
                'region_id'  => 7,
                'commune_id' => 1,
                'street'     => $this->faker->streetName(),
                'number'     => $this->faker->buildingNumber(),
                'apartment'  => null,
                'notes'      => null,
            ],
            'shipping_rate_name' => 'Despacho estándar RM',
            'paid_at'         => null,
            'shipped_at'      => null,
            'delivered_at'    => null,
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status'  => 'paid',
            'paid_at' => now(),
            'webpay_authorization_code' => $this->faker->numerify('######'),
            'webpay_card_last4'         => $this->faker->numerify('####'),
        ]);
    }
}
