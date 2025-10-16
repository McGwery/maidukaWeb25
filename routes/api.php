<?php

use App\Http\Controllers\Api\Auth\PhoneAuthController;
use App\Http\Controllers\Api\ShopController;
use Illuminate\Support\Facades\Route;

# Authentication Management
Route::prefix('auth')->group(function () {
    // Registration and verification routes
    Route::post('/register', [PhoneAuthController::class, 'register']);
    Route::post('/verify-phone', [PhoneAuthController::class, 'verifyPhone']);

    // Login routes
    Route::post('/login', [PhoneAuthController::class, 'login']);
    Route::post('/login/otp/request', [PhoneAuthController::class, 'requestLoginOtp']);
    Route::post('/login/otp/verify', [PhoneAuthController::class, 'loginWithOtp']);

    // Password reset routes
    Route::post('/password/reset/request', [PhoneAuthController::class, 'requestPasswordResetOtp'])->name('auth.password.reset.request');
    Route::post('/password/reset', [PhoneAuthController::class, 'resetPassword'])->name('auth.password.reset');
});

# Shop Management
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('shops')->group(function () {
        Route::get('/', [ShopController::class, 'index']);
        Route::post('/', [ShopController::class, 'store']);
        Route::get('/{shop}', [ShopController::class, 'show']);
        Route::put('/{shop}', [ShopController::class, 'update']);
        Route::post('/{shop}/switch', [ShopController::class, 'switchShop']);
    });
});