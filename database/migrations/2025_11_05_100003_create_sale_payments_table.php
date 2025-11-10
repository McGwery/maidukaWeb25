<?php

use App\Enums\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('payment_method', PaymentMethod::values());
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('payment_date');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('sale_id');
            $table->index('payment_method');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};

