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
        Schema::create('shop_savings_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->unique()->constrained()->cascadeOnDelete();

            // Savings settings
            $table->boolean('is_enabled')->default(false);
            $table->enum('savings_type', ['percentage', 'fixed_amount'])->default('percentage');
            $table->decimal('savings_percentage', 5, 2)->nullable(); // e.g., 10.50 for 10.5%
            $table->decimal('fixed_amount', 15, 2)->nullable(); // Fixed amount per day

            // Goal settings
            $table->decimal('target_amount', 15, 2)->nullable(); // Goal amount
            $table->date('target_date')->nullable(); // Goal deadline

            // Withdrawal settings
            $table->enum('withdrawal_frequency', ['none', 'weekly', 'bi_weekly', 'monthly', 'quarterly', 'when_goal_reached'])->default('monthly');
            $table->boolean('auto_withdraw')->default(false); // Auto withdraw when conditions met
            $table->decimal('minimum_withdrawal_amount', 15, 2)->nullable();

            // Tracking
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('total_saved', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->timestamp('last_savings_date')->nullable();
            $table->timestamp('last_withdrawal_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_savings_settings');
    }
};
