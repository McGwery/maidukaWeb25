<?php

namespace App\Models;

use App\Enums\AdPlacement;
use App\Enums\AdStatus;
use App\Enums\AdType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'title',
        'description',
        'image_url',
        'video_url',
        'media_type',
        'cta_text',
        'cta_url',
        'target_categories',
        'target_shop_types',
        'target_location',
        'target_all',
        'ad_type',
        'placement',
        'starts_at',
        'expires_at',
        'is_active',
        'budget',
        'cost_per_click',
        'total_spent',
        'view_count',
        'click_count',
        'unique_view_count',
        'unique_click_count',
        'ctr',
        'priority',
        'status',
        'rejection_reason',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'target_categories' => 'array',
        'target_shop_types' => 'array',
        'target_all' => 'boolean',
        'ad_type' => AdType::class,
        'placement' => AdPlacement::class,
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'budget' => 'decimal:2',
        'cost_per_click' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'view_count' => 'integer',
        'click_count' => 'integer',
        'unique_view_count' => 'integer',
        'unique_click_count' => 'integer',
        'ctr' => 'decimal:2',
        'priority' => 'integer',
        'status' => AdStatus::class,
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the shop that owns the ad.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the user who created the ad.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the ad.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get ad views.
     */
    public function views(): HasMany
    {
        return $this->hasMany(AdView::class);
    }

    /**
     * Get ad clicks.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(AdClick::class);
    }

    /**
     * Get ad conversions.
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(AdConversion::class);
    }

    /**
     * Get ad reports.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(AdReport::class);
    }

    /**
     * Get daily performance.
     */
    public function dailyPerformance(): HasMany
    {
        return $this->hasMany(AdPerformanceDaily::class);
    }

    /**
     * Check if ad is currently active and within date range.
     */
    public function isLive(): bool
    {
        return $this->is_active
            && $this->status === AdStatus::APPROVED
            && $this->starts_at <= now()
            && $this->expires_at >= now();
    }

    /**
     * Check if ad has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if ad is scheduled for future.
     */
    public function isScheduled(): bool
    {
        return $this->starts_at > now();
    }

    /**
     * Increment view count.
     */
    public function incrementViews(bool $unique = false): void
    {
        $this->increment('view_count');

        if ($unique) {
            $this->increment('unique_view_count');
        }

        $this->updateCTR();
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(bool $unique = false): void
    {
        $this->increment('click_count');

        if ($unique) {
            $this->increment('unique_click_count');
        }

        $this->updateCTR();

        // Update spent if cost per click is set
        if ($this->cost_per_click > 0) {
            $this->increment('total_spent', $this->cost_per_click);
        }
    }

    /**
     * Update click-through rate.
     */
    public function updateCTR(): void
    {
        if ($this->view_count > 0) {
            $ctr = ($this->click_count / $this->view_count) * 100;
            $this->update(['ctr' => round($ctr, 2)]);
        }
    }

    /**
     * Check if ad targets a specific shop.
     */
    public function targetsShop(Shop $shop): bool
    {
        // If targeting all, return true
        if ($this->target_all) {
            return true;
        }

        // Check shop type
        if (!empty($this->target_shop_types) && in_array($shop->business_type->value, $this->target_shop_types)) {
            return true;
        }

        // Check categories (if shop has categories - could be from products)
        if (!empty($this->target_categories)) {
            $shopCategories = $shop->products()->pluck('category_id')->unique()->toArray();
            $targetCategories = $this->target_categories;

            if (array_intersect($shopCategories, $targetCategories)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope to get active ads.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', AdStatus::APPROVED)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now());
    }

    /**
     * Scope to get ads by placement.
     */
    public function scopeByPlacement($query, string|AdPlacement $placement)
    {
        $placementValue = $placement instanceof AdPlacement ? $placement->value : $placement;

        return $query->where(function($q) use ($placementValue) {
            $q->where('placement', $placementValue)
              ->orWhere('placement', AdPlacement::ALL->value);
        });
    }

    /**
     * Scope to get ads for a specific shop.
     */
    public function scopeForShop($query, Shop $shop)
    {
        return $query->where(function($q) use ($shop) {
            // Targeting all
            $q->where('target_all', true);

            // Or targeting shop's business type
            $q->orWhereJsonContains('target_shop_types', $shop->business_type->value);

            // Or targeting shop's product categories
            $shopCategories = $shop->products()->pluck('category_id')->unique()->toArray();
            if (!empty($shopCategories)) {
                foreach ($shopCategories as $categoryId) {
                    $q->orWhereJsonContains('target_categories', $categoryId);
                }
            }
        });
    }

    /**
     * Scope to get admin ads (not from shops).
     */
    public function scopeAdminAds($query)
    {
        return $query->whereNull('shop_id');
    }

    /**
     * Scope to get shop ads.
     */
    public function scopeShopAds($query)
    {
        return $query->whereNotNull('shop_id');
    }
}

