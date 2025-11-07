<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopSettings extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',

        // General Business Information
        'business_email',
        'business_website',
        'tax_id',
        'registration_number',

        // Notifications Settings
        'enable_sms_notifications',
        'enable_email_notifications',
        'notify_low_stock',
        'low_stock_threshold',
        'notify_daily_sales_summary',
        'daily_summary_time',

        // Sales & POS Settings
        'auto_print_receipt',
        'allow_credit_sales',
        'credit_limit_days',
        'require_customer_for_credit',
        'allow_discounts',
        'max_discount_percentage',

        // Inventory Settings
        'track_stock',
        'allow_negative_stock',
        'auto_deduct_stock_on_sale',
        'stock_valuation_method',

        // Receipt/Invoice Settings
        'receipt_header',
        'receipt_footer',
        'show_shop_logo_on_receipt',
        'show_tax_on_receipt',
        'tax_percentage',

        // Working Hours
        'opening_time',
        'closing_time',
        'working_days',

        // Language & Regional
        'language',
        'timezone',
        'date_format',
        'time_format',

        // Security Settings
        'require_pin_for_refunds',
        'require_pin_for_discounts',
        'enable_two_factor_auth',

        // Backup & Data
        'auto_backup',
        'backup_frequency',
    ];

    protected $casts = [
        // Booleans
        'enable_sms_notifications' => 'boolean',
        'enable_email_notifications' => 'boolean',
        'notify_low_stock' => 'boolean',
        'notify_daily_sales_summary' => 'boolean',
        'auto_print_receipt' => 'boolean',
        'allow_credit_sales' => 'boolean',
        'require_customer_for_credit' => 'boolean',
        'allow_discounts' => 'boolean',
        'track_stock' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'auto_deduct_stock_on_sale' => 'boolean',
        'show_shop_logo_on_receipt' => 'boolean',
        'show_tax_on_receipt' => 'boolean',
        'require_pin_for_refunds' => 'boolean',
        'require_pin_for_discounts' => 'boolean',
        'enable_two_factor_auth' => 'boolean',
        'auto_backup' => 'boolean',

        // Numbers
        'low_stock_threshold' => 'integer',
        'credit_limit_days' => 'integer',
        'max_discount_percentage' => 'decimal:2',
        'tax_percentage' => 'decimal:2',

        // JSON
        'working_days' => 'array',
    ];

    /**
     * Get the shop that owns the settings.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get default settings.
     */
    public static function defaults(): array
    {
        return [
            'enable_sms_notifications' => true,
            'enable_email_notifications' => false,
            'notify_low_stock' => true,
            'low_stock_threshold' => 10,
            'notify_daily_sales_summary' => false,
            'daily_summary_time' => '18:00',
            'auto_print_receipt' => false,
            'allow_credit_sales' => true,
            'credit_limit_days' => 30,
            'require_customer_for_credit' => true,
            'allow_discounts' => true,
            'max_discount_percentage' => 20.00,
            'track_stock' => true,
            'allow_negative_stock' => false,
            'auto_deduct_stock_on_sale' => true,
            'stock_valuation_method' => 'fifo',
            'show_shop_logo_on_receipt' => true,
            'show_tax_on_receipt' => false,
            'tax_percentage' => 0.00,
            'opening_time' => '08:00',
            'closing_time' => '20:00',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'language' => 'sw',
            'timezone' => 'Africa/Dar_es_Salaam',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'require_pin_for_refunds' => true,
            'require_pin_for_discounts' => false,
            'enable_two_factor_auth' => false,
            'auto_backup' => false,
            'backup_frequency' => 'weekly',
        ];
    }

    /**
     * Check if shop is currently open based on settings.
     */
    public function isCurrentlyOpen(): bool
    {
        $now = now($this->timezone);
        $currentDay = strtolower($now->format('l'));

        // Check if today is a working day
        if (!in_array($currentDay, $this->working_days ?? [])) {
            return false;
        }

        // Check if within working hours
        $currentTime = $now->format('H:i');
        return $currentTime >= $this->opening_time && $currentTime <= $this->closing_time;
    }

    /**
     * Check if stock is low for a product.
     */
    public function isStockLow(int $quantity): bool
    {
        return $this->notify_low_stock && $quantity <= $this->low_stock_threshold;
    }
}

