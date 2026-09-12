<?php

namespace Tests\Feature;

use Database\Seeders\CouponSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponApplyApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed coupons
        $this->seed(CouponSeeder::class);
    }

    public function test_it_successfully_applies_fixed_discount(): void
    {
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'SAVE10',
            'cart_total' => 100.00,
            'user_id' => 1,
            'order_reference' => 'TEST-ORD-001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'reason' => null,
                'coupon_code' => 'SAVE10',
                'discount_amount' => 10.0,
                'final_total' => 90.0,
            ]);

        $this->assertDatabaseHas('coupon_redemptions', [
            'order_reference' => 'TEST-ORD-001',
            'amount_discounted' => 10.0,
        ]);
    }

    public function test_it_successfully_applies_percentage_discount(): void
    {
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'TENOFF',
            'cart_total' => 150.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'discount_amount' => 15.0,
                'final_total' => 135.0,
            ]);
    }

    public function test_it_caps_percentage_discount_at_max_discount_amount(): void
    {
        // HALFOFF is 50%, capped at 100
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'HALFOFF',
            'cart_total' => 300.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'discount_amount' => 100.0,
                'final_total' => 200.0,
            ]);
    }

    public function test_it_rejects_cart_below_minimum_order_value(): void
    {
        // MIN500 requires 500 minimum
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'MIN500',
            'cart_total' => 250.00,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'coupon_code' => 'MIN500',
                'discount_amount' => 0.0,
                'final_total' => 250.0,
            ]);

        $this->assertStringContainsString('Minimum order value', $response->json('reason'));
    }

    public function test_it_rejects_expired_coupon(): void
    {
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'EXPIRED10',
            'cart_total' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'reason' => 'This coupon has expired.',
            ]);
    }

    public function test_it_rejects_not_yet_active_coupon(): void
    {
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'COMINGSOON',
            'cart_total' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'reason' => 'This coupon is not active yet.',
            ]);
    }

    public function test_it_rejects_disabled_coupon(): void
    {
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'DISABLED10',
            'cart_total' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'reason' => 'This coupon is no longer active.',
            ]);
    }

    public function test_it_rejects_exhausted_usage_limit(): void
    {
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'ONLYONE',
            'cart_total' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'reason' => 'This coupon has reached its usage limit.',
            ]);
    }

    public function test_it_rejects_when_per_user_limit_exceeded(): void
    {
        // ONEPERUSER already used by user_id = 1 in seeder
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'ONEPERUSER',
            'cart_total' => 100.00,
            'user_id' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'reason' => 'You have already used this coupon the maximum number of times.',
            ]);
    }

    public function test_it_accepts_when_per_user_limit_not_exceeded_for_different_user(): void
    {
        // user_id = 99 has not used ONEPERUSER
        $response = $this->postJson('/api/coupons/apply', [
            'code' => 'ONEPERUSER',
            'cart_total' => 100.00,
            'user_id' => 99,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'discount_amount' => 25.0,
                'final_total' => 75.0,
            ]);
    }

    public function test_it_validates_request_format(): void
    {
        $response = $this->postJson('/api/coupons/apply', [
            'cart_total' => -10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'cart_total']);
    }

    public function test_it_returns_coupons_catalog_for_test_ui(): void
    {
        $response = $this->getJson('/test-api/coupons');

        $response->assertStatus(200)
            ->assertJsonIsArray()
            ->assertJsonStructure([
                '*' => ['id', 'code', 'type', 'value', 'formatted_value', 'min_order_value', 'status', 'status_badge']
            ]);
    }

    public function test_it_returns_redemptions_for_test_ui(): void
    {
        $response = $this->getJson('/test-api/redemptions');

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    public function test_it_resets_database_state_for_test_ui(): void
    {
        $response = $this->postJson('/test-api/reset');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
