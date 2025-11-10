<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsGoal extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'target_amount',
        'target_date',
        'current_amount',
        'amount_withdrawn',
        'progress_percentage',
        'status',
        'completed_at',
        'started_at',
        'icon',
        'color',
        'priority',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'target_date' => 'date',
        'current_amount' => 'decimal:2',
        'amount_withdrawn' => 'decimal:2',
        'progress_percentage' => 'integer',
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'priority' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($goal) {
            if (!$goal->started_at) {
                $goal->started_at = now();
            }
        });

        static::updating(function ($goal) {
            // Auto-update progress percentage
            $goal->progress_percentage = $goal->calculateProgress();

            // Auto-complete if target reached
            if ($goal->current_amount >= $goal->target_amount && $goal->status === 'active') {
                $goal->status = 'completed';
                $goal->completed_at = now();
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    /**
     * Calculate progress percentage
     */
    public function calculateProgress(): int
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        $percentage = ($this->current_amount / $this->target_amount) * 100;
        return min(100, (int) $percentage);
    }

    /**
     * Get remaining amount to reach goal
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Check if goal is achieved
     */
    public function isAchieved(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Scope for active goals
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed goals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
