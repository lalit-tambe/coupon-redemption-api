<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $couponService)
    {
    }

    public function apply(ApplyCouponRequest $request): JsonResponse
    {
        // Coupon application logic will come here
    }
}
