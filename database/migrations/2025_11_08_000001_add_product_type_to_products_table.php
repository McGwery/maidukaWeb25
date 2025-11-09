<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ProductType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add product_type column after category_id
            $table->enum('product_type', ProductType::values())
                  ->default(ProductType::PHYSICAL->value)
                  ->after('category_id');

            // Add service-specific fields
            $table->decimal('service_duration', 8, 2)->nullable()->after('product_type')
                  ->comment('Duration in hours for service products');
            $table->decimal('hourly_rate', 15, 2)->nullable()->after('service_duration')
                  ->comment('Hourly rate for service products');

            // Make inventory-related fields nullable for services
            $table->integer('purchase_quantity')->nullable()->change();
            $table->decimal('total_amount_paid', 15, 2)->nullable()->change();
            $table->decimal('cost_per_unit', 15, 2)->nullable()->change();
            $table->integer('current_stock')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'service_duration', 'hourly_rate']);

            // Revert nullable changes (note: might lose data if nulls exist)
            $table->integer('purchase_quantity')->nullable(false)->change();
            $table->decimal('total_amount_paid', 15, 2)->nullable(false)->change();
            $table->decimal('cost_per_unit', 15, 2)->nullable(false)->change();
            $table->integer('current_stock')->nullable(false)->change();
        });
    }
};

