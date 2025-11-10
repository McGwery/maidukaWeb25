<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdView extends Model
{
    use HasUuid;

    protected $fillable = [
        'ad_id',
        'user_id',
        'shop_id',
        'ip_address',
        'user_agent',
        'device_type',
        'platform',
        'viewed_at',
        'view_duration',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'view_duration' => 'integer',
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

