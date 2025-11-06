<?php

namespace App\Models;

use App\Enums\SaleStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'customer_id',
        'user_id',
        'sale_number',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'discount_percentage',
        'total_amount',
        'amount_paid',
        'change_amount',
        'debt_amount',
        'profit_amount',
        'status',
        'payment_status',
        'notes',
        'sale_date',
        'converted_to_expense_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'status' => SaleStatus::class,
        'sale_date' => 'datetime',
        'converted_to_expense_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (!$sale->sale_number) {
                $sale->sale_number = static::generateSaleNumber($sale->shop_id);
            }
            if (!$sale->sale_date) {
                $sale->sale_date = now();
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(SaleRefund::class);
    }

    public static function generateSaleNumber(string $shopId): string
    {
        $date = now()->format('Ymd');
        $lastSale = static::where('shop_id', $shopId)
            ->whereDate('created_at', now())
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = $lastSale ? (int) substr($lastSale->sale_number, -4) + 1 : 1;

        return 'SAL-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partially_paid';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function canRefund(): bool
    {
        return in_array($this->status, [SaleStatus::COMPLETED]) && $this->amount_paid > 0;
    }

    public function getTotalRefunded(): float
    {
        return $this->refunds()->sum('amount');
    }

    public function getRemainingDebt(): float
    {
        return max(0, $this->total_amount - $this->amount_paid);
    }
}

