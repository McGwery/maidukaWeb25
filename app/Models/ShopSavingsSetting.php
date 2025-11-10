<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopSavingsSetting extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'is_enabled',
        'savings_type',
        'savings_percentage',
        'fixed_amount',
        'target_amount',
        'target_date',
        'withdrawal_frequency',
        'auto_withdraw',
        'minimum_withdrawal_amount',
        'current_balance',
        'total_saved',
        'total_withdrawn',
        'last_savings_date',
        'last_withdrawal_date',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'savings_percentage' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'target_date' => 'date',
        'auto_withdraw' => 'boolean',
        'minimum_withdrawal_amount' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'total_saved' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'last_savings_date' => 'datetime',
        'last_withdrawal_date' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class, 'shop_id', 'shop_id');
    }

    /**
     * Calculate savings amount based on daily profit
     */
    public function calculateSavingsAmount(float $dailyProfit): float
    {
        if (!$this->is_enabled) {
            return 0;
        }

        if ($this->savings_type === 'percentage') {
            return ($dailyProfit * $this->savings_percentage) / 100;
        }

        return $this->fixed_amount ?? 0;
    }

    /**
     * Check if withdrawal is due based on frequency
     */
    public function isWithdrawalDue(): bool
    {
        if (!$this->auto_withdraw || $this->withdrawal_frequency === 'none') {
            return false;
        }

        if ($this->withdrawal_frequency === 'when_goal_reached') {
            return $this->target_amount && $this->current_balance >= $this->target_amount;
        }

        if (!$this->last_withdrawal_date) {
            return true; // First withdrawal
        }

        $daysSinceLastWithdrawal = now()->diffInDays($this->last_withdrawal_date);

        return match ($this->withdrawal_frequency) {
            'weekly' => $daysSinceLastWithdrawal >= 7,
            'bi_weekly' => $daysSinceLastWithdrawal >= 14,
            'monthly' => $daysSinceLastWithdrawal >= 30,
            'quarterly' => $daysSinceLastWithdrawal >= 90,
            default => false,
        };
    }

    /**
     * Check if minimum withdrawal amount is met
     */
    public function canWithdraw(): bool
    {
        if ($this->minimum_withdrawal_amount) {
            return $this->current_balance >= $this->minimum_withdrawal_amount;
        }

        return $this->current_balance > 0;
    }

    /**
     * Get progress percentage towards goal
     */
    public function getProgressPercentage(): int
    {
        if (!$this->target_amount || $this->target_amount <= 0) {
            return 0;
        }

        $percentage = ($this->current_balance / $this->target_amount) * 100;
        return min(100, (int) $percentage);
    }
}
