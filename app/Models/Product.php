<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'category_id',
        'product_name',
        'description',
        'sku',
        'barcode',
        'purchase_quantity',
        'total_amount_paid',
        'cost_per_unit',
        'unit_type',
        'break_down_count_per_unit',
        'small_item_name',
        'sell_whole_units',
        'price_per_unit',
        'sell_individual_items',
        'price_per_item',
        'sell_in_bundles',
        'current_stock',
        'low_stock_threshold',
        'track_inventory',
        'image_url',
    ];

    protected $casts = [
        'unit_type' => UnitType::class,
        'sell_whole_units' => 'boolean',
        'sell_individual_items' => 'boolean',
        'sell_in_bundles' => 'boolean',
        'track_inventory' => 'boolean',
        'purchase_quantity' => 'integer',
        'break_down_count_per_unit' => 'integer',
        'current_stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'total_amount_paid' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'price_per_item' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function isLowStock(): bool
    {
        if (!$this->track_inventory || !$this->low_stock_threshold) {
            return false;
        }

        return $this->current_stock <= $this->low_stock_threshold;
    }

    public function getTotalItems(): ?int
    {
        if (!$this->sell_individual_items || !$this->break_down_count_per_unit) {
            return null;
        }

        return $this->current_stock * $this->break_down_count_per_unit;
    }

    /**
     * Calculate current inventory value (capital invested)
     */
    public function getInventoryValue(): float
    {
        return $this->current_stock * $this->cost_per_unit;
    }

    /**
     * Calculate expected revenue if all stock is sold at whole unit price
     */
    public function getExpectedRevenue(): float
    {
        $revenue = 0;

        if ($this->sell_whole_units && $this->price_per_unit) {
            $revenue += $this->current_stock * $this->price_per_unit;
        }

        if ($this->sell_individual_items && $this->price_per_item && $this->break_down_count_per_unit) {
            $totalItems = $this->current_stock * $this->break_down_count_per_unit;
            $revenue += $totalItems * $this->price_per_item;
        }

        return $revenue;
    }

    /**
     * Calculate expected profit if all stock is sold
     */
    public function getExpectedProfit(): float
    {
        return $this->getExpectedRevenue() - $this->getInventoryValue();
    }

    /**
     * Calculate expected profit margin percentage
     */
    public function getExpectedProfitMargin(): float
    {
        $inventoryValue = $this->getInventoryValue();

        if ($inventoryValue == 0) {
            return 0;
        }

        return ($this->getExpectedProfit() / $inventoryValue) * 100;
    }

    /**
     * Get total value lost from damaged/lost stock
     */
    public function getTotalLosses(): float
    {
        return $this->stockAdjustments()
            ->whereIn('type', [
                \App\Enums\StockAdjustmentType::DAMAGED->value,
                \App\Enums\StockAdjustmentType::EXPIRED->value,
                \App\Enums\StockAdjustmentType::LOST->value,
                \App\Enums\StockAdjustmentType::THEFT->value,
            ])
            ->get()
            ->sum(fn($adjustment) => $adjustment->getMonetaryImpact());
    }
}
