<?php

namespace App\Models;

use App\Enums\StockAdjustmentType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'value_at_time',
        'previous_stock',
        'new_stock',
        'reason',
        'notes',
    ];

    protected $casts = [
        'type' => StockAdjustmentType::class,
        'quantity' => 'integer',
        'value_at_time' => 'decimal:2',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the monetary impact of this adjustment
     */
    public function getMonetaryImpact(): float
    {
        return abs($this->quantity) * $this->value_at_time;
    }

    /**
     * Check if this adjustment reduced stock
     */
    public function isReduction(): bool
    {
        return $this->quantity < 0;
    }
}

