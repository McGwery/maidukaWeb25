<?php

namespace App\Models;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'plan',
        'type',
        'status',
        'price',
        'currency',
        'starts_at',
        'expires_at',
        'auto_renew',
        'payment_method',
        'transaction_reference',
        'features',
        'max_users',
        'max_products',
        'notes',
        'cancelled_at',
        'cancelled_reason',
    ];

    protected $casts = [
        'plan' => SubscriptionPlan::class,
        'type' => SubscriptionType::class,
        'status' => SubscriptionStatus::class,
        'currency' => \App\Enums\Currency::class,
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'features' => 'array',
        'price' => 'decimal:2',
        'max_users' => 'integer',
        'max_products' => 'integer',
    ];

    /**
     * Get the shop that owns the subscription.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE
            && $this->expires_at > now();
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Get days remaining until expiration.
     */
    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->expires_at);
    }

    /**
     * Check if subscription is expiring soon (within 7 days).
     */
    public function isExpiringSoon(): bool
    {
        return $this->daysRemaining() <= 7 && $this->daysRemaining() > 0;
    }

    /**
     * Renew the subscription.
     */
    public function renew(int $days = null): self
    {
        $days = $days ?? $this->plan->durationDays();

        $this->update([
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'expires_at' => now()->addDays($days),
        ]);

        return $this;
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(string $reason = null): self
    {
        $this->update([
            'status' => SubscriptionStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancelled_reason' => $reason,
            'auto_renew' => false,
        ]);

        return $this;
    }

    /**
     * Suspend the subscription.
     */
    public function suspend(): self
    {
        $this->update([
            'status' => SubscriptionStatus::SUSPENDED,
        ]);

        return $this;
    }

    /**
     * Activate the subscription.
     */
    public function activate(): self
    {
        $this->update([
            'status' => SubscriptionStatus::ACTIVE,
        ]);

        return $this;
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope to get expiring soon subscriptions.
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->whereBetween('expires_at', [now(), now()->addDays(7)]);
    }
}

