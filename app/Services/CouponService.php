<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate a coupon against a cart and, if it is valid, record the
     * redemption atomically.
     *
     * Decision: for this exercise, a successful "apply" call *is* the
     * redemption (no separate order-confirmation step exists here). In a
     * real system this would likely be split into apply() [preview, no
     * write] and redeem() [called on payment confirmation] — documented
     * as a scope decision in the README.
     *
     * @return array{
     *   valid: bool,
     *   reason: string|null,
     *   coupon_code: string|null,
     *   discount_amount: float,
     *   final_total: float
     * }
     */
    public function apply(string $code, float $cartTotal, ?int $userId = null, ?string $orderReference = null): array
    {
        return DB::transaction(function () use ($code, $cartTotal, $userId, $orderReference) {
            // lockForUpdate() closes the race condition where two concurrent
            // requests both read "usage_limit not yet reached" and both pass.
            $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
                ->lockForUpdate()
                ->first();

            if (!$coupon) {
                return $this->invalid('Coupon code not found.', $cartTotal);
            }

            if (!$coupon->active) {
                return $this->invalid('This coupon is no longer active.', $cartTotal, $coupon->code);
            }

            $now = Carbon::now();

            if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
                return $this->invalid('This coupon is not active yet.', $cartTotal, $coupon->code);
            }

            if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
                return $this->invalid('This coupon has expired.', $cartTotal, $coupon->code);
            }

            if ($cartTotal < (float) $coupon->min_order_value) {
                return $this->invalid(
                    sprintf('Minimum order value for this coupon is %.2f.', $coupon->min_order_value),
                    $cartTotal,
                    $coupon->code
                );
            }

            if ($coupon->usage_limit !== null) {
                $totalUses = $coupon->redemptions()->count();
                if ($totalUses >= $coupon->usage_limit) {
                    return $this->invalid('This coupon has reached its usage limit.', $cartTotal, $coupon->code);
                }
            }

            if ($userId !== null && $coupon->usage_limit_per_user !== null) {
                $userUses = $coupon->redemptions()->where('user_id', $userId)->count();
                if ($userUses >= $coupon->usage_limit_per_user) {
                    return $this->invalid(
                        'You have already used this coupon the maximum number of times.',
                        $cartTotal,
                        $coupon->code
                    );
                }
            }

            $discount = $this->calculateDiscount($coupon, $cartTotal);
            $finalTotal = round($cartTotal - $discount, 2);

            CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'user_id' => $userId,
                'order_reference' => $orderReference,
                'amount_discounted' => $discount,
                'redeemed_at' => $now,
            ]);

            return [
                'valid' => true,
                'reason' => null,
                'coupon_code' => $coupon->code,
                'discount_amount' => $discount,
                'final_total' => $finalTotal,
            ];
        });
    }

    private function calculateDiscount(Coupon $coupon, float $cartTotal): float
    {
        if ($coupon->type === Coupon::TYPE_FIXED) {
            // A fixed discount can never take the total below zero.
            return round(min((float) $coupon->value, $cartTotal), 2);
        }

        $discount = $cartTotal * ((float) $coupon->value / 100);

        if ($coupon->max_discount_amount !== null) {
            $discount = min($discount, (float) $coupon->max_discount_amount);
        }

        return round(min($discount, $cartTotal), 2);
    }

    private function invalid(string $reason, float $cartTotal, ?string $code = null): array
    {
        return [
            'valid' => false,
            'reason' => $reason,
            'coupon_code' => $code,
            'discount_amount' => 0.0,
            'final_total' => round($cartTotal, 2),
        ];
    }
}
