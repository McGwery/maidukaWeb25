<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopSavingsSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,
            'isEnabled' => $this->is_enabled,
            'savingsType' => $this->savings_type,
            'savingsPercentage' => (float) $this->savings_percentage,
            'fixedAmount' => (float) $this->fixed_amount,
            'targetAmount' => (float) $this->target_amount,
            'targetDate' => $this->target_date?->toDateString(),
            'withdrawalFrequency' => $this->withdrawal_frequency,
            'autoWithdraw' => $this->auto_withdraw,
            'minimumWithdrawalAmount' => (float) $this->minimum_withdrawal_amount,
            'currentBalance' => (float) $this->current_balance,
            'totalSaved' => (float) $this->total_saved,
            'totalWithdrawn' => (float) $this->total_withdrawn,
            'lastSavingsDate' => $this->last_savings_date?->toDateString(),
            'lastWithdrawalDate' => $this->last_withdrawal_date?->toDateString(),
            'progressPercentage' => $this->getProgressPercentage(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

