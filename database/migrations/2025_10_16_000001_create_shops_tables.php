<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('business_type');
            $table->string('phone_number', 20)->nullable();
            $table->string('address', 1000);
            $table->string('agent_code')->nullable()->unique();
            $table->string('currency');
            $table->string('image_url', 2048)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'is_active']);
            $table->index('agent_code');
        });

        Schema::create('shop_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('role');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['shop_id', 'user_id']);
        });

        Schema::create('active_shops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('shop_id')->constrained()->onDelete('cascade');
            $table->timestamp('selected_at');
            $table->unique('user_id');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_shops');
        Schema::dropIfExists('shop_members');
        Schema::dropIfExists('shops');
    }
};