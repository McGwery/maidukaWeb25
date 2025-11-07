<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'shop_one_id',
        'shop_two_id',
        'last_message',
        'last_message_by',
        'last_message_at',
        'is_active',
        'is_archived_by_shop_one',
        'is_archived_by_shop_two',
        'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
        'is_archived_by_shop_one' => 'boolean',
        'is_archived_by_shop_two' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function shopOne(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_one_id');
    }

    public function shopTwo(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_two_id');
    }

    public function lastMessageUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_message_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function typingIndicators(): HasMany
    {
        return $this->hasMany(TypingIndicator::class);
    }

    // Helper methods
    public function getOtherShop(string $shopId): ?Shop
    {
        if ($this->shop_one_id === $shopId) {
            return $this->shopTwo;
        } elseif ($this->shop_two_id === $shopId) {
            return $this->shopOne;
        }
        return null;
    }

    public function getUnreadCount(string $shopId): int
    {
        return $this->messages()
            ->where('receiver_shop_id', $shopId)
            ->where('is_read', false)
            ->count();
    }

    public function isArchived(string $shopId): bool
    {
        if ($this->shop_one_id === $shopId) {
            return $this->is_archived_by_shop_one;
        } elseif ($this->shop_two_id === $shopId) {
            return $this->is_archived_by_shop_two;
        }
        return false;
    }

    public function markAsArchived(string $shopId, bool $archived = true): void
    {
        if ($this->shop_one_id === $shopId) {
            $this->is_archived_by_shop_one = $archived;
        } elseif ($this->shop_two_id === $shopId) {
            $this->is_archived_by_shop_two = $archived;
        }
        $this->save();
    }

    public function updateLastMessage(Message $message): void
    {
        $this->update([
            'last_message' => $message->message,
            'last_message_by' => $message->sender_user_id,
            'last_message_at' => $message->created_at,
        ]);
    }

    // Scopes
    public function scopeForShop($query, string $shopId)
    {
        return $query->where(function ($q) use ($shopId) {
            $q->where('shop_one_id', $shopId)
                ->orWhere('shop_two_id', $shopId);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotArchived($query, string $shopId)
    {
        return $query->where(function ($q) use ($shopId) {
            $q->where(function ($subQ) use ($shopId) {
                $subQ->where('shop_one_id', $shopId)
                    ->where('is_archived_by_shop_one', false);
            })->orWhere(function ($subQ) use ($shopId) {
                $subQ->where('shop_two_id', $shopId)
                    ->where('is_archived_by_shop_two', false);
            });
        });
    }

    public static function findOrCreateConversation(string $shopOneId, string $shopTwoId): self
    {
        // Ensure consistent ordering
        $shops = [$shopOneId, $shopTwoId];
        sort($shops);

        return self::firstOrCreate(
            [
                'shop_one_id' => $shops[0],
                'shop_two_id' => $shops[1],
            ],
            [
                'is_active' => true,
            ]
        );
    }
}

