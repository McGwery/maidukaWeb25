<?php

use App\Http\Controllers\Api\Auth\PhoneAuthController;
use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\POSController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\SavingsController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ShopMemberController;
use App\Http\Controllers\Api\ShopSettingsController;
use App\Http\Controllers\Api\SubscriptionController;
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

            // Stock adjustment
            Route::patch('/{product}/stock', [ProductController::class, 'updateStock']);
            Route::get('/{product}/adjustments', [ProductController::class, 'stockAdjustmentHistory']);
        });

        // Inventory Analysis
        Route::get('/{shop}/inventory/analysis', [ProductController::class, 'inventoryAnalysis']);
        Route::get('/{shop}/inventory/adjustments', [ProductController::class, 'adjustmentsSummary']);

        // Purchase Order Management
        Route::group(['prefix' => '{shop}/purchase-orders'], function () {
            // Buyer routes - orders I've created
            Route::get('/buyer', [PurchaseOrderController::class, 'indexAsBuyer']);

            // Seller routes - orders others have created with my shop
            Route::get('/seller', [PurchaseOrderController::class, 'indexAsSeller']);

            // CRUD operations
            Route::post('/', [PurchaseOrderController::class, 'store']);
            Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
            Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
            Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);

            // Status management
            Route::patch('/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus']);

            // Payment management
            Route::post('/{purchaseOrder}/payments', [PurchaseOrderController::class, 'recordPayment']);

            // Stock transfer
            Route::post('/{purchaseOrder}/transfer-stock', [PurchaseOrderController::class, 'transferStock']);
        });

        // POS - Sales Management
        Route::group(['prefix' => '{shop}/pos'], function () {
            // Complete sale
            Route::post('/sales', [POSController::class, 'completeSale']);

            // Sales history and details
            Route::get('/sales', [POSController::class, 'getSales']);
            Route::get('/sales/{sale}', [POSController::class, 'getSale']);

            // Sales analytics
            Route::get('/analytics', [POSController::class, 'getSalesAnalytics']);

            // Refund and payments
            Route::post('/sales/{sale}/refund', [POSController::class, 'refundSale']);
            Route::post('/sales/{sale}/payments', [POSController::class, 'addPayment']);
        });

        // Customer Management
        Route::group(['prefix' => '{shop}/customers'], function () {
            Route::get('/', [POSController::class, 'getCustomers']);
            Route::post('/', [POSController::class, 'createCustomer']);
            Route::get('/{customer}', [POSController::class, 'getCustomer']);
            Route::put('/{customer}', [POSController::class, 'updateCustomer']);
            Route::delete('/{customer}', [POSController::class, 'deleteCustomer']);
        });

        // Expense Management
        Route::group(['prefix' => '{shop}/expenses'], function () {
            Route::get('/', [ExpenseController::class, 'index']);
            Route::post('/', [ExpenseController::class, 'store']);
            Route::get('/summary', [ExpenseController::class, 'summary']);
            Route::get('/categories', [ExpenseController::class, 'categories']);
            Route::get('/{expense}', [ExpenseController::class, 'show']);
            Route::put('/{expense}', [ExpenseController::class, 'update']);
            Route::delete('/{expense}', [ExpenseController::class, 'destroy']);
        });

        // Reports & Analytics
        Route::group(['prefix' => '{shop}/reports'], function () {
            Route::get('/overview', [ReportsController::class, 'overviewReport']);
            Route::get('/sales', [ReportsController::class, 'salesReport']);
            Route::get('/products', [ReportsController::class, 'productsReport']);
            Route::get('/financial', [ReportsController::class, 'financialReport']);
            Route::get('/employees', [ReportsController::class, 'employeesReport']);
        });

        // Shop Settings
        Route::group(['prefix' => '{shop}/settings'], function () {
            Route::get('/', [ShopSettingsController::class, 'show']);
            Route::put('/', [ShopSettingsController::class, 'update']);
            Route::post('/reset', [ShopSettingsController::class, 'reset']);
        });

        // Savings & Goals Management
        Route::group(['prefix' => '{shop}/savings'], function () {
            // Settings
            Route::get('/settings', [SavingsController::class, 'getSettings']);
            Route::put('/settings', [SavingsController::class, 'updateSettings']);

            // Transactions
            Route::post('/deposit', [SavingsController::class, 'deposit']);
            Route::post('/withdraw', [SavingsController::class, 'withdraw']);
            Route::get('/transactions', [SavingsController::class, 'getTransactions']);
            Route::get('/summary', [SavingsController::class, 'getSummary']);

            // Goals
            Route::get('/goals', [SavingsController::class, 'getGoals']);
            Route::post('/goals', [SavingsController::class, 'createGoal']);
            Route::put('/goals/{goal}', [SavingsController::class, 'updateGoal']);
            Route::delete('/goals/{goal}', [SavingsController::class, 'deleteGoal']);
        });

        // Subscription Management
        Route::group(['prefix' => '{shop}/subscriptions'], function () {
            // Get all subscriptions
            Route::get('/', [SubscriptionController::class, 'index']);

            // Get current active subscription
            Route::get('/current', [SubscriptionController::class, 'current']);

            // Get subscription statistics
            Route::get('/statistics', [SubscriptionController::class, 'statistics']);

            // Create new subscription
            Route::post('/', [SubscriptionController::class, 'store']);

            // Get specific subscription
            Route::get('/{subscription}', [SubscriptionController::class, 'show']);

            // Update subscription
            Route::put('/{subscription}', [SubscriptionController::class, 'update']);

            // Subscription actions
            Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
            Route::post('/{subscription}/renew', [SubscriptionController::class, 'renew']);
            Route::post('/{subscription}/suspend', [SubscriptionController::class, 'suspend']);
            Route::post('/{subscription}/activate', [SubscriptionController::class, 'activate']);
        });

        // Ads & Promotions
        Route::group(['prefix' => 'ads'], function () {
            // Get all ads for shop
            Route::get('/', [AdController::class, 'index']);

            // Create new ad
            Route::post('/', [AdController::class, 'store']);

            // Get specific ad
            Route::get('/{ad}', [AdController::class, 'show']);

            // Update ad
            Route::put('/{ad}', [AdController::class, 'update']);

            // Delete ad
            Route::delete('/{ad}', [AdController::class, 'destroy']);

            // Track view
            Route::post('/{ad}/view', [AdController::class, 'trackView']);

            // Track click
            Route::post('/{ad}/click', [AdController::class, 'trackClick']);

            // Get analytics
            Route::get('/{ad}/analytics', [AdController::class, 'analytics']);

            // Admin actions
            Route::post('/{ad}/approve', [AdController::class, 'approve']);
            Route::post('/{ad}/reject', [AdController::class, 'reject']);
            Route::post('/{ad}/toggle-pause', [AdController::class, 'togglePause']);
        });

    });

    // Ads Feed (Deals Tab) - Outside shop context
    Route::get('/ads/feed', [AdController::class, 'feed']);

    // Subscription Plans (outside shop context)
    Route::get('/subscription-plans', [SubscriptionController::class, 'plans']);

    // Settings Categories (outside shop context)
    Route::get('/settings-categories', [ShopSettingsController::class, 'categories']);
});
