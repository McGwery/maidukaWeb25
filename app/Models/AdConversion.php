<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdConversion extends Model
{
    use HasUuid;

    protected $fillable = [
        'ad_id',
        'user_id',
        'shop_id',
        'conversion_type',
        'conversion_value',
        'conversion_data',
        'converted_at',
    ];

    protected $casts = [
        'conversion_value' => 'decimal:2',
        'converted_at' => 'datetime',
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}

