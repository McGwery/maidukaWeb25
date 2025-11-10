<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'buyer_shop_id',
        'seller_shop_id',
        'reference_number',
        'status',
        'total_amount',
        'total_paid',
        'notes',
        'approved_at',
        'approved_by',
        'is_internal',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'total_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_internal' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchaseOrder) {
            if (!$purchaseOrder->reference_number) {
                $purchaseOrder->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));

        return "{$prefix}-{$date}-{$random}";
    }

    public function buyerShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'buyer_shop_id');
    }

    public function sellerShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'seller_shop_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return (float) ($this->total_amount - $this->total_paid);
    }

    public function isFullyPaid(): bool
    {
        return $this->total_paid >= $this->total_amount;
    }

    public function canBeApproved(): bool
    {
        return $this->status === PurchaseOrderStatus::PENDING;
    }

    public function canBeCompleted(): bool
    {
        return $this->status === PurchaseOrderStatus::APPROVED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            PurchaseOrderStatus::PENDING,
            PurchaseOrderStatus::APPROVED
        ]);
    }
}
