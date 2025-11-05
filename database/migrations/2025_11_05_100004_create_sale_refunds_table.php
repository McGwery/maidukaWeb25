<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamp('refund_date');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('sale_id');
            $table->index('refund_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_refunds');
    }
};

