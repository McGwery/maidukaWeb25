<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}