<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();

            // Product snapshot
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit_type')->nullable();

            // Pricing
            $table->decimal('original_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->decimal('cost_price', 15, 2);

            // Discounts
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);

            // Tax
            $table->decimal('tax_amount', 15, 2)->default(0);

            // Totals
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('profit', 15, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};

