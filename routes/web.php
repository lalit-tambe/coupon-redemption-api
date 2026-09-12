<?php

use App\Http\Controllers\TestCouponController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('coupon-test');
});

Route::prefix('test-api')->group(function () {
    Route::get('/coupons', [TestCouponController::class, 'coupons']);
    Route::get('/redemptions', [TestCouponController::class, 'redemptions']);
    Route::post('/reset', [TestCouponController::class, 'reset']);
});
