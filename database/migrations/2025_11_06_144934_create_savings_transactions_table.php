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
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('savings_goal_id')->nullable()->constrained()->nullOnDelete();

            // Transaction details
            $table->enum('type', ['deposit', 'withdrawal']); // deposit = saving, withdrawal = taking out
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);

            // Source information
            $table->date('transaction_date');
            $table->decimal('daily_profit', 15, 2)->nullable(); // Profit that triggered the savings
            $table->boolean('is_automatic')->default(true); // Auto-saved or manual

            // Additional info
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['shop_id', 'type']);
            $table->index(['shop_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
    }
};
