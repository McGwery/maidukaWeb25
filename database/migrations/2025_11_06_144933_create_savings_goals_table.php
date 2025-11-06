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
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->constrained()->cascadeOnDelete();

            // Goal details
            $table->string('name'); // e.g., "New Equipment", "Shop Expansion", "Emergency Fund"
            $table->text('description')->nullable();
            $table->decimal('target_amount', 15, 2);
            $table->date('target_date')->nullable();

            // Progress tracking
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->decimal('amount_withdrawn', 15, 2)->default(0);
            $table->integer('progress_percentage')->default(0); // 0-100

            // Status
            $table->enum('status', ['active', 'completed', 'cancelled', 'paused'])->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('started_at')->nullable();

            // Settings
            $table->string('icon')->nullable(); // For UI display
            $table->string('color')->nullable(); // For UI display
            $table->integer('priority')->default(0); // For ordering multiple goals

            $table->timestamps();
            $table->softDeletes();

            $table->index(['shop_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};
