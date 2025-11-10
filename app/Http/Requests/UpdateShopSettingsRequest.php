<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // General Business Information
            'businessEmail' => 'nullable|email',
            'businessWebsite' => 'nullable|url',
            'taxId' => 'nullable|string|max:50',
            'registrationNumber' => 'nullable|string|max:50',

            // Notifications Settings
            'enableSmsNotifications' => 'nullable|boolean',
            'enableEmailNotifications' => 'nullable|boolean',
            'notifyLowStock' => 'nullable|boolean',
            'lowStockThreshold' => 'nullable|integer|min:0|max:1000',
            'notifyDailySalesSummary' => 'nullable|boolean',
            'dailySummaryTime' => 'nullable|date_format:H:i',

            // Sales & POS Settings
            'autoPrintReceipt' => 'nullable|boolean',
            'allowCreditSales' => 'nullable|boolean',
            'creditLimitDays' => 'nullable|integer|min:1|max:365',
            'requireCustomerForCredit' => 'nullable|boolean',
            'allowDiscounts' => 'nullable|boolean',
            'maxDiscountPercentage' => 'nullable|numeric|min:0|max:100',

            // Inventory Settings
            'trackStock' => 'nullable|boolean',
            'allowNegativeStock' => 'nullable|boolean',
            'autoDeductStockOnSale' => 'nullable|boolean',
            'stockValuationMethod' => 'nullable|in:fifo,lifo,average',

            // Receipt/Invoice Settings
            'receiptHeader' => 'nullable|string|max:500',
            'receiptFooter' => 'nullable|string|max:500',
            'showShopLogoOnReceipt' => 'nullable|boolean',
            'showTaxOnReceipt' => 'nullable|boolean',
            'taxPercentage' => 'nullable|numeric|min:0|max:100',

            // Working Hours
            'openingTime' => 'nullable|date_format:H:i',
            'closingTime' => 'nullable|date_format:H:i',
            'workingDays' => 'nullable|array',
            'workingDays.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',

            // Language & Regional
            'language' => 'nullable|in:sw,en',
            'timezone' => 'nullable|timezone',
            'dateFormat' => 'nullable|string|max:20',
            'timeFormat' => 'nullable|string|max:20',

            // Security Settings
            'requirePinForRefunds' => 'nullable|boolean',
            'requirePinForDiscounts' => 'nullable|boolean',
            'enableTwoFactorAuth' => 'nullable|boolean',

            // Backup & Data
            'autoBackup' => 'nullable|boolean',
            'backupFrequency' => 'nullable|in:daily,weekly,monthly',
        ];
    }
}

