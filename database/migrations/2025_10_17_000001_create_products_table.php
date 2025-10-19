<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UnitType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            
            // Basic product information
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            
            // Purchase information
            $table->integer('purchase_quantity');
            $table->decimal('total_amount_paid', 15, 2);
            $table->decimal('cost_per_unit', 15, 2);
            
            // Unit configuration
            $table->enum('unit_type', UnitType::values());
            $table->integer('break_down_count_per_unit')->nullable();
            $table->string('small_item_name')->nullable();
            
            // Selling configuration
            $table->boolean('sell_whole_units')->default(true);
            $table->decimal('price_per_unit', 15, 2)->nullable();
            $table->boolean('sell_individual_items')->default(false);
            $table->decimal('price_per_item', 15, 2)->nullable();
            $table->boolean('sell_in_bundles')->default(false);
            
            // Stock management
            $table->integer('current_stock');
            $table->integer('low_stock_threshold')->nullable();
            $table->boolean('track_inventory')->default(true);
            
            // Media
            $table->string('image_url')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['shop_id', 'category_id']);
            $table->index('product_name');
            $table->index('sku');
            $table->index('barcode');
            $table->index(['current_stock', 'low_stock_threshold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};