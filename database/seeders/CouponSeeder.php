<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Seeds a variety of coupons covering every rule the feature enforces,
     * so you can manually hit the endpoint and exercise each path without
     * creating data by hand in tinker every time.
     */
    public function run(): void
    {
        // --- Straightforward, always-valid coupons ---

        Coupon::updateOrCreate([
            'code' => 'SAVE10',
            'type' => 'fixed',
            'value' => 10,
            'min_order_value' => 0,
        ]);

        Coupon::updateOrCreate([
            'code' => 'SAVE50',
            'type' => 'fixed',
            'value' => 50,
            'min_order_value' => 200,
        ]);

        Coupon::updateOrCreate([
            'code' => 'TENOFF',
            'type' => 'percentage',
            'value' => 10,
            'min_order_value' => 0,
        ]);

        // --- Percentage coupon with a discount cap ---

        Coupon::updateOrCreate([
            'code' => 'HALFOFF',
            'type' => 'percentage',
            'value' => 50,
            'min_order_value' => 0,
            'max_discount_amount' => 100, // 50% off, capped at 100
        ]);

        // --- Minimum order value rejection ---

        Coupon::updateOrCreate([
            'code' => 'MIN500',
            'type' => 'fixed',
            'value' => 75,
            'min_order_value' => 500,
        ]);

        // --- Expired coupon ---

        Coupon::updateOrCreate([
            'code' => 'EXPIRED10',
            'type' => 'fixed',
            'value' => 10,
            'expires_at' => now()->subDays(3),
        ]);

        // --- Not yet started coupon ---

        Coupon::updateOrCreate([
            'code' => 'COMINGSOON',
            'type' => 'fixed',
            'value' => 20,
            'starts_at' => now()->addDays(7),
        ]);

        // --- Inactive / disabled coupon ---

        Coupon::updateOrCreate([
            'code' => 'DISABLED10',
            'type' => 'fixed',
            'value' => 10,
            'active' => false,
        ]);

        // --- Global usage limit, already exhausted ---

        $exhausted = Coupon::updateOrCreate([
            'code' => 'ONLYONE',
            'type' => 'fixed',
            'value' => 15,
            'usage_limit' => 1,
        ]);

        CouponRedemption::updateOrCreate([
            'coupon_id' => $exhausted->id,
            'user_id' => null,
            'order_reference' => 'seed-existing-redemption',
            'amount_discounted' => 15,
            'redeemed_at' => now()->subHour(),
        ]);

        // --- Per-user usage limit, already used by user_id = 1 ---

        $perUserLimited = Coupon::updateOrCreate([
            'code' => 'ONEPERUSER',
            'type' => 'fixed',
            'value' => 25,
            'usage_limit_per_user' => 1,
        ]);

        CouponRedemption::updateOrCreate([
            'coupon_id' => $perUserLimited->id,
            'user_id' => 1,
            'order_reference' => 'seed-user-1-redemption',
            'amount_discounted' => 25,
            'redeemed_at' => now()->subHour(),
        ]);

        $this->command->info('Seeded 10 coupons covering valid, expired, inactive, min-order, and usage-limit cases.');
    }
}
