<?php

use App\Http\Controllers\Api\V1\AgeStageController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\GuideController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ShippingRateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);

    Route::get('age-stages', [AgeStageController::class, 'index']);
    Route::get('brands', [BrandController::class, 'index']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);

    Route::get('guides', [GuideController::class, 'index']);
    Route::get('guides/{slug}', [GuideController::class, 'show']);

    Route::get('shipping-rates', [ShippingRateController::class, 'index']);

    Route::post('coupons/validate', [CouponController::class, 'validate']);
});
