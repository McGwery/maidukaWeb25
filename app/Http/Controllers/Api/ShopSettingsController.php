<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateShopSettingsRequest;
use App\Http\Resources\ShopSettingsResource;
use App\Models\Shop;
use App\Models\ShopSettings;
use App\Policies\ShopSettingsPolicy;
use App\Traits\HasStandardResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ShopSettingsController extends Controller
{
    use HasStandardResponse;

    /**
     * Get shop settings.
     */
    public function show(Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
//        Gate::authorize('view', [ShopSettingsPolicy::class, $shop]);

        // Get or create settings with defaults
        $settings = $shop->settings;

        if (!$settings) {
            $settings = ShopSettings::create(array_merge(
                ['shop_id' => $shop->id],
                ShopSettings::defaults()
            ));
        }

        return $this->successResponse(
            'Shop settings retrieved successfully.',
            new ShopSettingsResource($settings)
        );
    }

    /**
     * Update shop settings.
     */
    public function update(UpdateShopSettingsRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
//        Gate::authorize('update', [ShopSettingsPolicy::class, $shop]);

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Get or create settings
            $settings = $shop->settings;

            if (!$settings) {
                $settings = ShopSettings::create(array_merge(
                    ['shop_id' => $shop->id],
                    ShopSettings::defaults()
                ));
            }

            // Convert camelCase to snake_case for database
            $updateData = [];

            foreach ($data as $key => $value) {
                $snakeKey = $this->camelToSnake($key);
                $updateData[$snakeKey] = $value;
            }

            $settings->update($updateData);

            DB::commit();

            return $this->successResponse(
                'Shop settings updated successfully.',
                new ShopSettingsResource($settings->fresh())
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to update shop settings.',
                ['error' => $e->getMessage()],
                Response::HTTP_OK
            );
        }
    }

    /**
     * Reset settings to defaults.
     */
    public function reset(Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
//        Gate::authorize('reset', [ShopSettingsPolicy::class, $shop]);

        try {
            DB::beginTransaction();

            $settings = $shop->settings;

            if (!$settings) {
                $settings = ShopSettings::create(array_merge(
                    ['shop_id' => $shop->id],
                    ShopSettings::defaults()
                ));
            } else {
                $settings->update(ShopSettings::defaults());
            }

            DB::commit();

            return $this->successResponse(
                'Shop settings reset to defaults successfully.',
                new ShopSettingsResource($settings->fresh())
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to reset shop settings.',
                ['error' => $e->getMessage()],
                Response::HTTP_OK
            );
        }
    }

    /**
     * Get settings categories for easier UI organization.
     */
    public function categories(): JsonResponse
    {
        $this->initRequestTime();

        $categories = [
            [
                'key' => 'general',
                'label' => 'General Information',
                'icon' => 'info',
                'fields' => ['businessEmail', 'businessWebsite', 'taxId', 'registrationNumber']
            ],
            [
                'key' => 'notifications',
                'label' => 'Notifications',
                'icon' => 'bell',
                'fields' => ['enableSmsNotifications', 'enableEmailNotifications', 'notifyLowStock', 'lowStockThreshold', 'notifyDailySalesSummary', 'dailySummaryTime']
            ],
            [
                'key' => 'sales',
                'label' => 'Sales & POS',
                'icon' => 'shopping-cart',
                'fields' => ['autoPrintReceipt', 'allowCreditSales', 'creditLimitDays', 'requireCustomerForCredit', 'allowDiscounts', 'maxDiscountPercentage']
            ],
            [
                'key' => 'inventory',
                'label' => 'Inventory',
                'icon' => 'package',
                'fields' => ['trackStock', 'allowNegativeStock', 'autoDeductStockOnSale', 'stockValuationMethod']
            ],
            [
                'key' => 'receipt',
                'label' => 'Receipt & Invoice',
                'icon' => 'file-text',
                'fields' => ['receiptHeader', 'receiptFooter', 'showShopLogoOnReceipt', 'showTaxOnReceipt', 'taxPercentage']
            ],
            [
                'key' => 'hours',
                'label' => 'Working Hours',
                'icon' => 'clock',
                'fields' => ['openingTime', 'closingTime', 'workingDays']
            ],
            [
                'key' => 'regional',
                'label' => 'Language & Regional',
                'icon' => 'globe',
                'fields' => ['language', 'timezone', 'dateFormat', 'timeFormat']
            ],
            [
                'key' => 'security',
                'label' => 'Security',
                'icon' => 'shield',
                'fields' => ['requirePinForRefunds', 'requirePinForDiscounts', 'enableTwoFactorAuth']
            ],
            [
                'key' => 'backup',
                'label' => 'Backup & Data',
                'icon' => 'database',
                'fields' => ['autoBackup', 'backupFrequency']
            ],
        ];

        return $this->successResponse(
            'Settings categories retrieved successfully.',
            ['categories' => $categories]
        );
    }

    /**
     * Convert camelCase to snake_case.
     */
    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}

