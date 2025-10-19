<?php

use App\Http\Controllers\Api\Auth\PhoneAuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ShopMemberController;
use Illuminate\Support\Facades\Route;

# Authentication Management
Route::prefix('auth')->group(function () {
    // Registration and verification routes
    Route::post('/register', [PhoneAuthController::class, 'register']);
    Route::post('/verify-phone', [PhoneAuthController::class, 'verifyPhone']);

    // Login routes
    Route::post('/login', [PhoneAuthController::class, 'loginWithPassword']);
    Route::post('/login/otp/request', [PhoneAuthController::class, 'requestLoginOtp']);
    Route::post('/login/otp/verify', [PhoneAuthController::class, 'loginWithOtp']);

    // Password reset routes
    Route::post('/password/reset/request', [PhoneAuthController::class, 'requestPasswordResetOtp'])->name('auth.password.reset.request');
    Route::post('/password/reset', [PhoneAuthController::class, 'resetPassword'])->name('auth.password.reset');

    Route::post('/logout', [PhoneAuthController::class, 'logout'])->middleware('auth:sanctum');
});


Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('shops')->group(function () {

        //  Shop Management
        Route::get('/', [ShopController::class, 'index']);
        Route::post('/', [ShopController::class, 'store']);
        Route::get('/{shop}', [ShopController::class, 'show']);
        Route::put('/{shop}', [ShopController::class, 'update']);
        Route::delete('/{shop}', [ShopController::class, 'destroy']);
        Route::post('/{shop}/switch', [ShopController::class, 'switchShop']);
        Route::post('/{shop}/active', [ShopController::class, 'setActive']);

        // Categories Management
        Route::prefix('categories/ctx')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
        });

        // Shop Members Management
        Route::group(['prefix' => '{shop}/members'], function () {
            Route::get('/', [ShopMemberController::class, 'index']);
            Route::post('/', [ShopMemberController::class, 'store']);
            Route::get('/{member}', [ShopMemberController::class, 'show']);
            Route::put('/{member}', [ShopMemberController::class, 'update']);
            Route::delete('/{member}', [ShopMemberController::class, 'destroy']);
        });

        // Product Management
        Route::group(['prefix' => '{shop}/products'], function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/{product}', [ProductController::class, 'show']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::delete('/{product}', [ProductController::class, 'destroy']);
            Route::patch('/{product}/stock', [ProductController::class, 'updateStock']);
        });

        // Purchase Order Management
        Route::group(['prefix' => '{shop}/purchase-orders'], function () {
            // Buyer routes - orders I've created
            Route::get('/buyer', [PurchaseOrderController::class, 'indexAsBuyer']);

            // Seller routes - orders others have created with my shop
            Route::get('/seller', [PurchaseOrderController::class, 'indexAsSeller']);

            // CRUD operations
            Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
            Route::post('/', [PurchaseOrderController::class, 'store']);
            Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
            Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);

            // Status management
            Route::patch('/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus']);

            // Payment management
            Route::post('/{purchaseOrder}/payments', [PurchaseOrderController::class, 'recordPayment']);

            // Stock transfer
            Route::post('/{purchaseOrder}/transfer-stock', [PurchaseOrderController::class, 'transferStock']);
        });

    });
});
