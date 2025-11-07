<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,

            // General Business Information
            'businessEmail' => $this->business_email,
            'businessWebsite' => $this->business_website,
            'taxId' => $this->tax_id,
            'registrationNumber' => $this->registration_number,

            // Notifications Settings
            'enableSmsNotifications' => $this->enable_sms_notifications,
            'enableEmailNotifications' => $this->enable_email_notifications,
            'notifyLowStock' => $this->notify_low_stock,
            'lowStockThreshold' => $this->low_stock_threshold,
            'notifyDailySalesSummary' => $this->notify_daily_sales_summary,
            'dailySummaryTime' => $this->daily_summary_time,

            // Sales & POS Settings
            'autoPrintReceipt' => $this->auto_print_receipt,
            'allowCreditSales' => $this->allow_credit_sales,
            'creditLimitDays' => $this->credit_limit_days,
            'requireCustomerForCredit' => $this->require_customer_for_credit,
            'allowDiscounts' => $this->allow_discounts,
            'maxDiscountPercentage' => $this->max_discount_percentage,

            // Inventory Settings
            'trackStock' => $this->track_stock,
            'allowNegativeStock' => $this->allow_negative_stock,
            'autoDeductStockOnSale' => $this->auto_deduct_stock_on_sale,
            'stockValuationMethod' => $this->stock_valuation_method,

            // Receipt/Invoice Settings
            'receiptHeader' => $this->receipt_header,
            'receiptFooter' => $this->receipt_footer,
            'showShopLogoOnReceipt' => $this->show_shop_logo_on_receipt,
            'showTaxOnReceipt' => $this->show_tax_on_receipt,
            'taxPercentage' => $this->tax_percentage,

            // Working Hours
            'openingTime' => $this->opening_time,
            'closingTime' => $this->closing_time,
            'workingDays' => $this->working_days,
            'isCurrentlyOpen' => $this->isCurrentlyOpen(),

            // Language & Regional
            'language' => $this->language,
            'timezone' => $this->timezone,
            'dateFormat' => $this->date_format,
            'timeFormat' => $this->time_format,

            // Security Settings
            'requirePinForRefunds' => $this->require_pin_for_refunds,
            'requirePinForDiscounts' => $this->require_pin_for_discounts,
            'enableTwoFactorAuth' => $this->enable_two_factor_auth,

            // Backup & Data
            'autoBackup' => $this->auto_backup,
            'backupFrequency' => $this->backup_frequency,

            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

