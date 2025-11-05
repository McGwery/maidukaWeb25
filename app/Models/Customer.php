<?php

namespace App\Models;

use App\Enums\SaleStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'phone',
        'email',
        'address',
        'credit_limit',
        'current_debt',
        'total_purchases',
        'total_paid',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_debt' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function hasAvailableCredit(float $amount): bool
    {
        if (!$this->credit_limit) {
            return false;
        }

        return ($this->current_debt + $amount) <= $this->credit_limit;
    }

    public function addDebt(float $amount): void
    {
        $this->increment('current_debt', $amount);
        $this->increment('total_purchases', $amount);
    }

    public function reduceDebt(float $amount): void
    {
        $this->decrement('current_debt', $amount);
        $this->increment('total_paid', $amount);
    }
}

