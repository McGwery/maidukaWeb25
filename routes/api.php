<?php

use App\Http\Controllers\Api\Auth\PhoneAuthController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ShopMemberController;
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

    Route::post('/logout', [PhoneAuthController::class, 'logout'])->middleware('auth:sanctum');
});


Route::middleware('auth:sanctum')->group(function () {
    //  Shop Management
    Route::prefix('shops')->group(function () {
        Route::get('/', [ShopController::class, 'index']);
        Route::post('/', [ShopController::class, 'store']);
        Route::get('/{shop}', [ShopController::class, 'show']);
        Route::put('/{shop}', [ShopController::class, 'update']);
        Route::delete('/{shop}', [ShopController::class, 'destroy']);
        Route::post('/{shop}/switch', [ShopController::class, 'switchShop']);
        Route::post('/{shop}/active', [ShopController::class, 'setActive']);
        Route::get('/active', [ShopController::class, 'getActive']);

        // Shop Members Management
        Route::get('/{shop}/members', [ShopMemberController::class, 'index']);
        Route::post('/{shop}/members', [ShopMemberController::class, 'store']);
        Route::get('/{shop}/members/{member}', [ShopMemberController::class, 'show']);
        Route::put('/{shop}/members/{member}', [ShopMemberController::class, 'update']);
        Route::delete('/{shop}/members/{member}', [ShopMemberController::class, 'destroy']);
    });
});
