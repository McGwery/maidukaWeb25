<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedShop extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'shop_id',
        'blocked_shop_id',
        'blocked_by',
        'reason',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function blockedShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'blocked_shop_id');
    }

    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public static function isBlocked(string $shopId, string $otherShopId): bool
    {
        return self::where('shop_id', $shopId)
            ->where('blocked_shop_id', $otherShopId)
            ->exists();
    }
}

