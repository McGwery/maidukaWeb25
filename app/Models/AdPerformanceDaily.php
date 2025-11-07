<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdPerformanceDaily extends Model
{
    use HasUuid;

    protected $fillable = [
        'ad_id',
        'date',
        'views',
        'unique_views',
        'clicks',
        'unique_clicks',
        'conversions',
        'ctr',
        'conversion_rate',
        'cost',
    ];

    protected $casts = [
        'date' => 'date',
        'views' => 'integer',
        'unique_views' => 'integer',
        'clicks' => 'integer',
        'unique_clicks' => 'integer',
        'conversions' => 'integer',
        'ctr' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }
}

