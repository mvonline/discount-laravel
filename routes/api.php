<?php

use Illuminate\Support\Facades\Route;
use Coupone\DiscountManager\Http\Controllers\DiscountCodeController;
use Coupone\DiscountManager\Http\Controllers\DiscountValidationController;
use Coupone\DiscountManager\Http\Controllers\SwaggerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Swagger Documentation Routes
Route::get('documentation', [SwaggerController::class, 'index']);
Route::get('documentation.json', [SwaggerController::class, 'json']);
Route::get('documentation.yaml', [SwaggerController::class, 'yaml']);

Route::prefix('discount-codes')->group(function () {
    // CRUD endpoints
    Route::get('/', [DiscountCodeController::class, 'index']);
    Route::post('/', [DiscountCodeController::class, 'store']);
    Route::get('/{discountCode}', [DiscountCodeController::class, 'show']);
    Route::put('/{discountCode}', [DiscountCodeController::class, 'update']);
    Route::delete('/{discountCode}', [DiscountCodeController::class, 'destroy']);

    // Discount validation endpoints
    Route::post('/validate', [DiscountValidationController::class, 'validate']);
    Route::post('/maximum-discount', [DiscountValidationController::class, 'getMaximumDiscount']);
    Route::post('/{discountCode}/track-usage', [DiscountValidationController::class, 'trackUsage']);
}); 