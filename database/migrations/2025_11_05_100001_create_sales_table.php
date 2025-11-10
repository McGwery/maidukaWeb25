<?php

use App\Enums\SaleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            // Sale information
            $table->string('sale_number')->unique();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            // Payment information
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->decimal('debt_amount', 15, 2)->default(0);

            // Profit tracking
            $table->decimal('profit_amount', 15, 2)->default(0);

            // Status
            $table->enum('status', SaleStatus::values())->default(SaleStatus::COMPLETED->value);
            $table->enum('payment_status', ['paid', 'partially_paid', 'pending', 'debt'])->default('paid');

            $table->text('notes')->nullable();
            $table->timestamp('sale_date');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['shop_id', 'sale_date']);
            $table->index(['shop_id', 'status']);
            $table->index(['shop_id', 'customer_id']);
            $table->index(['shop_id', 'payment_status']);
            $table->index('sale_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

