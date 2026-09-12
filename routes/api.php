<?php

use App\Http\Controllers\Api\CouponController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Add this route to your app's routes/api.php
|--------------------------------------------------------------------------
*/

Route::post('/coupons/apply', [CouponController::class, 'apply']);
