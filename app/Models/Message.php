<?php

namespace App\Models;

use App\Enums\MessageType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_shop_id',
        'sender_user_id',
        'receiver_shop_id',
        'message',
        'message_type',
        'attachments',
        'product_id',
        'location_lat',
        'location_lng',
        'location_name',
        'is_read',
        'read_at',
        'is_delivered',
        'delivered_at',
        'reply_to_message_id',
        'is_deleted_by_sender',
        'is_deleted_by_receiver',
    ];

    protected $casts = [
        'message_type' => MessageType::class,
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_delivered' => 'boolean',
        'delivered_at' => 'datetime',
        'is_deleted_by_sender' => 'boolean',
        'is_deleted_by_receiver' => 'boolean',
    ];

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function senderShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'sender_shop_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function receiverShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'receiver_shop_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    // Helper methods
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function markAsDelivered(): void
    {
        if (!$this->is_delivered) {
            $this->update([
                'is_delivered' => true,
                'delivered_at' => now(),
            ]);
        }
    }

    public function isDeletedFor(string $shopId): bool
    {
        if ($this->sender_shop_id === $shopId) {
            return $this->is_deleted_by_sender;
        } elseif ($this->receiver_shop_id === $shopId) {
            return $this->is_deleted_by_receiver;
        }
        return false;
    }

    public function deleteFor(string $shopId): void
    {
        if ($this->sender_shop_id === $shopId) {
            $this->is_deleted_by_sender = true;
        } elseif ($this->receiver_shop_id === $shopId) {
            $this->is_deleted_by_receiver = true;
        }
        $this->save();
    }

    // Scopes
    public function scopeForShop($query, string $shopId)
    {
        return $query->where(function ($q) use ($shopId) {
            $q->where('sender_shop_id', $shopId)
                ->orWhere('receiver_shop_id', $shopId);
        });
    }

    public function scopeNotDeleted($query, string $shopId)
    {
        return $query->where(function ($q) use ($shopId) {
            $q->where(function ($subQ) use ($shopId) {
                $subQ->where('sender_shop_id', $shopId)
                    ->where('is_deleted_by_sender', false);
            })->orWhere(function ($subQ) use ($shopId) {
                $subQ->where('receiver_shop_id', $shopId)
                    ->where('is_deleted_by_receiver', false);
            });
        });
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByType($query, string|MessageType $type)
    {
        $typeValue = $type instanceof MessageType ? $type->value : $type;
        return $query->where('message_type', $typeValue);
    }
}

