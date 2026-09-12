<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $couponService)
    {
    }

    public function apply(ApplyCouponRequest $request): JsonResponse
    {
        $result = $this->couponService->apply(
            code: $request->validated('code'),
            cartTotal: (float) $request->validated('cart_total'),
            userId: $request->validated('user_id'),
            orderReference: $request->validated('order_reference'),
        );

        return response()->json($result, $result['valid'] ? 200 : 422);
    }
}
