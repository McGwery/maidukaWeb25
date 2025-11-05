<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'title',
        'description',
        'category',
        'amount',
        'expense_date',
        'payment_method',
        'receipt_number',
        'attachment_url',
        'recorded_by',
    ];

    protected $casts = [
        'category' => \App\Enums\ExpenseCategory::class,
        'payment_method' => \App\Enums\PaymentMethod::class,
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

