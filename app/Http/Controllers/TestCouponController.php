<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use Database\Seeders\CouponSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestCouponController extends Controller
{
    /**
     * Get all available coupons with their redemption counts and status.
     */
    public function coupons(): JsonResponse
    {
        $now = Carbon::now();

        $coupons = Coupon::withCount('redemptions')
            ->orderBy('id')
            ->get()
            ->map(function (Coupon $coupon) use ($now) {
                $status = 'active';
                $statusBadge = 'Active';

                if (!$coupon->active) {
                    $status = 'disabled';
                    $statusBadge = 'Disabled';
                } elseif ($coupon->starts_at && $now->lt($coupon->starts_at)) {
                    $status = 'scheduled';
                    $statusBadge = 'Starts ' . $coupon->starts_at->format('M d');
                } elseif ($coupon->expires_at && $now->gt($coupon->expires_at)) {
                    $status = 'expired';
                    $statusBadge = 'Expired';
                } elseif ($coupon->usage_limit !== null && $coupon->redemptions_count >= $coupon->usage_limit) {
                    $status = 'exhausted';
                    $statusBadge = 'Limit Reached';
                }

                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => (float) $coupon->value,
                    'formatted_value' => $coupon->type === Coupon::TYPE_PERCENTAGE
                        ? (float) $coupon->value . '%'
                        : '$' . number_format((float) $coupon->value, 2),
                    'min_order_value' => (float) $coupon->min_order_value,
                    'max_discount_amount' => $coupon->max_discount_amount !== null ? (float) $coupon->max_discount_amount : null,
                    'usage_limit' => $coupon->usage_limit,
                    'usage_limit_per_user' => $coupon->usage_limit_per_user,
                    'redemptions_count' => $coupon->redemptions_count,
                    'remaining_uses' => $coupon->usage_limit !== null
                        ? max(0, $coupon->usage_limit - $coupon->redemptions_count)
                        : null,
                    'starts_at' => $coupon->starts_at?->toIso8601String(),
                    'expires_at' => $coupon->expires_at?->toIso8601String(),
                    'active' => (bool) $coupon->active,
                    'status' => $status,
                    'status_badge' => $statusBadge,
                ];
            });

        return response()->json($coupons);
    }

    /**
     * Get recent redemptions for the live audit log.
     */
    public function redemptions(): JsonResponse
    {
        $redemptions = CouponRedemption::with('coupon:id,code')
            ->latest('id')
            ->limit(25)
            ->get()
            ->map(function (CouponRedemption $redemption) {
                return [
                    'id' => $redemption->id,
                    'coupon_code' => $redemption->coupon?->code ?? 'N/A',
                    'user_id' => $redemption->user_id,
                    'order_reference' => $redemption->order_reference ?? '—',
                    'amount_discounted' => (float) $redemption->amount_discounted,
                    'redeemed_at' => $redemption->redeemed_at?->diffForHumans() ?? $redemption->created_at?->diffForHumans(),
                    'redeemed_at_exact' => $redemption->redeemed_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($redemptions);
    }

    /**
     * Reset and re-seed the coupons and test redemptions.
     */
    public function reset(): JsonResponse
    {
        // Temporarily disable foreign key constraints to safely truncate redemptions and coupons
        Schema::disableForeignKeyConstraints();
        CouponRedemption::truncate();
        Coupon::truncate();
        Schema::enableForeignKeyConstraints();

        // Run the seeder to restore fresh seed data
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CouponSeeder', '--force' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Database reset: test coupons restored and usage limits re-initialized!',
        ]);
    }
}
