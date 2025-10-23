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
        Schema::create('shop_suppliers', function (Blueprint $table) {
            $table->id();
            $table->uuid('shop_id');
            $table->uuid('supplier_id');
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('shops')->onDelete('cascade');

            // Ensure a shop can't add the same supplier twice
            $table->unique(['shop_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_suppliers');
    }
};
