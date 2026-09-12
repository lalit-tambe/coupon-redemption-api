<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('SAVE##??')),
            'type' => Coupon::TYPE_FIXED,
            'value' => 10,
            'min_order_value' => 0,
            'max_discount_amount' => null,
            'usage_limit' => null,
            'usage_limit_per_user' => null,
            'starts_at' => null,
            'expires_at' => null,
            'active' => true,
        ];
    }

    public function percentage(int $value = 20, ?float $cap = null): static
    {
        return $this->state(fn () => [
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => $value,
            'max_discount_amount' => $cap,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function notYetStarted(): static
    {
        return $this->state(fn () => ['starts_at' => now()->addDay()]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
