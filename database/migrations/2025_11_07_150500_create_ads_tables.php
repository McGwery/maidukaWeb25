<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main ads table
        Schema::create('ads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->string('cta_text')->default('Learn More');
            $table->string('cta_url')->nullable();

            // Targeting
            $table->json('target_categories')->nullable();
            $table->json('target_shop_types')->nullable();
            $table->string('target_location')->nullable();
            $table->boolean('target_all')->default(false);

            // Ad type and placement
            $table->enum('ad_type', ['banner', 'card', 'popup', 'native'])->default('card');
            $table->enum('placement', ['home', 'products', 'sales', 'reports', 'all'])->default('home');

            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            // Budget & Billing
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('cost_per_click', 10, 2)->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);

            // Analytics counters
            $table->integer('view_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->integer('unique_view_count')->default(0);
            $table->integer('unique_click_count')->default(0);
            $table->decimal('ctr', 5, 2)->default(0);

            // Priority and status
            $table->integer('priority')->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'paused', 'completed'])->default('draft');
            $table->text('rejection_reason')->nullable();

            // Admin tracking
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_active', 'starts_at', 'expires_at']);
            $table->index('shop_id');
            $table->index(['placement', 'ad_type']);
        });

        Schema::create('ad_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('platform')->nullable();
            $table->timestamp('viewed_at');
            $table->integer('view_duration')->nullable();

            $table->timestamps();

            $table->index(['ad_id', 'viewed_at']);
            $table->index(['user_id', 'ad_id']);
        });

        Schema::create('ad_clicks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('platform')->nullable();
            $table->string('click_location')->nullable();
            $table->timestamp('clicked_at');

            $table->timestamps();

            $table->index(['ad_id', 'clicked_at']);
            $table->index(['user_id', 'ad_id']);
        });

        Schema::create('ad_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('conversion_type', ['visit', 'call', 'message', 'purchase', 'signup'])->default('visit');
            $table->decimal('conversion_value', 10, 2)->nullable();
            $table->text('conversion_data')->nullable();
            $table->timestamp('converted_at');

            $table->timestamps();

            $table->index(['ad_id', 'conversion_type']);
        });

        Schema::create('ad_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('reported_by')->constrained('users')->cascadeOnDelete();
            $table->enum('reason', ['spam', 'inappropriate', 'misleading', 'offensive', 'other']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'action_taken', 'dismissed'])->default('pending');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();

            $table->index(['ad_id', 'status']);
        });

        Schema::create('ad_performance_daily', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('unique_views')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('unique_clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('ctr', 5, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('cost', 10, 2)->default(0);

            $table->timestamps();

            $table->unique(['ad_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_performance_daily');
        Schema::dropIfExists('ad_reports');
        Schema::dropIfExists('ad_conversions');
        Schema::dropIfExists('ad_clicks');
        Schema::dropIfExists('ad_views');
        Schema::dropIfExists('ads');
    }
};

