<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponRedemptionFactory extends Factory
{
    protected $model = CouponRedemption::class;

    public function definition(): array
    {
        return [
            'coupon_id' => Coupon::factory(),
            'user_id' => null,
            'order_reference' => $this->faker->uuid(),
            'amount_discounted' => 10,
            'redeemed_at' => now(),
        ];
    }
}
