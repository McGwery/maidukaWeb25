<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\StockAdjustmentType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            // Adjustment details
            $table->enum('type', StockAdjustmentType::values());
            $table->integer('quantity'); // Negative for reductions, positive for additions
            $table->decimal('value_at_time', 15, 2); // Cost per unit at the time of adjustment

            // Stock tracking
            $table->integer('previous_stock');
            $table->integer('new_stock');

            // Documentation
            $table->string('reason');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['product_id', 'type']);
            $table->index('created_at');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};

