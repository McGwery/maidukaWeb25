<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conversations between shops
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_one_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignUuid('shop_two_id')->constrained('shops')->cascadeOnDelete();

            // Last message info for quick display
            $table->text('last_message')->nullable();
            $table->foreignUuid('last_message_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived_by_shop_one')->default(false);
            $table->boolean('is_archived_by_shop_two')->default(false);

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Ensure unique conversation between two shops
            $table->unique(['shop_one_id', 'shop_two_id']);
            $table->index(['shop_one_id', 'is_active']);
            $table->index(['shop_two_id', 'is_active']);
            $table->index('last_message_at');
        });

        // Messages within conversations
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('sender_shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignUuid('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('receiver_shop_id')->constrained('shops')->cascadeOnDelete();

            // Message content
            $table->text('message')->nullable();
            $table->enum('message_type', ['text', 'image', 'video', 'audio', 'document', 'product', 'location'])->default('text');

            // Media attachments
            $table->json('attachments')->nullable();

            // Product reference (if sharing product)
            $table->foreignUuid('product_id')->nullable()->constrained()->nullOnDelete();

            // Location data
            $table->string('location_lat')->nullable();
            $table->string('location_lng')->nullable();
            $table->string('location_name')->nullable();

            // Read status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            // Delivery status
            $table->boolean('is_delivered')->default(false);
            $table->timestamp('delivered_at')->nullable();

            // Reply reference
            $table->foreignUuid('reply_to_message_id')->nullable()->constrained('messages')->nullOnDelete();

            // Deletion
            $table->boolean('is_deleted_by_sender')->default(false);
            $table->boolean('is_deleted_by_receiver')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['receiver_shop_id', 'is_read']);
            $table->index('sender_shop_id');
        });

        // Typing indicators
        Schema::create('typing_indicators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('expires_at');

            $table->timestamps();

            $table->index(['conversation_id', 'expires_at']);
        });

        // Message reactions
        Schema::create('message_reactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('message_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('reaction'); // emoji or reaction type

            $table->timestamps();

            $table->unique(['message_id', 'user_id']);
            $table->index('message_id');
        });

        // Blocked shops (for blocking spam/unwanted conversations)
        Schema::create('blocked_shops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('blocked_shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignUuid('blocked_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->unique(['shop_id', 'blocked_shop_id']);
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_shops');
        Schema::dropIfExists('message_reactions');
        Schema::dropIfExists('typing_indicators');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};

