<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->unique()->constrained()->cascadeOnDelete();

            // General Business Information
            $table->string('business_email')->nullable();
            $table->string('business_website')->nullable();
            $table->string('tax_id')->nullable(); // TIN number
            $table->string('registration_number')->nullable();

            // Notifications Settings
            $table->boolean('enable_sms_notifications')->default(true);
            $table->boolean('enable_email_notifications')->default(false);
            $table->boolean('notify_low_stock')->default(true);
            $table->integer('low_stock_threshold')->default(10);
            $table->boolean('notify_daily_sales_summary')->default(false);
            $table->string('daily_summary_time')->default('18:00'); // 6 PM

            // Sales & POS Settings
            $table->boolean('auto_print_receipt')->default(false);
            $table->boolean('allow_credit_sales')->default(true);
            $table->integer('credit_limit_days')->default(30);
            $table->boolean('require_customer_for_credit')->default(true);
            $table->boolean('allow_discounts')->default(true);
            $table->decimal('max_discount_percentage', 5, 2)->default(20.00);

            // Inventory Settings
            $table->boolean('track_stock')->default(true);
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('auto_deduct_stock_on_sale')->default(true);
            $table->string('stock_valuation_method')->default('fifo'); // fifo, lifo, average

            // Receipt/Invoice Settings
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->boolean('show_shop_logo_on_receipt')->default(true);
            $table->boolean('show_tax_on_receipt')->default(false);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);

            // Working Hours
            $table->string('opening_time')->default('08:00');
            $table->string('closing_time')->default('20:00');
            $table->json('working_days')->nullable(); // ["monday", "tuesday", ...]

            // Language & Regional
            $table->string('language')->default('sw'); // sw = Swahili, en = English
            $table->string('timezone')->default('Africa/Dar_es_Salaam');
            $table->string('date_format')->default('d/m/Y');
            $table->string('time_format')->default('H:i');

            // Security Settings
            $table->boolean('require_pin_for_refunds')->default(true);
            $table->boolean('require_pin_for_discounts')->default(false);
            $table->boolean('enable_two_factor_auth')->default(false);

            // Backup & Data
            $table->boolean('auto_backup')->default(false);
            $table->string('backup_frequency')->default('weekly'); // daily, weekly, monthly

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};
