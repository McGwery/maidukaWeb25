<?php

use App\Http\Controllers\Api\Auth\PhoneAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Registration and verification routes
    Route::post('/register', [PhoneAuthController::class, 'register']);
    Route::post('/verify-phone', [PhoneAuthController::class, 'verifyPhone']);

    // Login routes
    Route::post('/login', [PhoneAuthController::class, 'login']);
    Route::post('/login/otp/request', [PhoneAuthController::class, 'requestLoginOtp']);
    Route::post('/login/otp/verify', [PhoneAuthController::class, 'loginWithOtp']);

    // Password reset routes
    Route::post('/password/reset/request', [PhoneAuthController::class, 'requestPasswordResetOtp']);
    Route::post('/password/reset', [PhoneAuthController::class, 'resetPassword']);
});