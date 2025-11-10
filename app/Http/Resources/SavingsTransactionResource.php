<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,
            'savingsGoalId' => $this->savings_goal_id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'balanceBefore' => (float) $this->balance_before,
            'balanceAfter' => (float) $this->balance_after,
            'transactionDate' => $this->transaction_date->toDateString(),
            'dailyProfit' => (float) $this->daily_profit,
            'isAutomatic' => $this->is_automatic,
            'description' => $this->description,
            'notes' => $this->notes,
            'processedBy' => $this->whenLoaded('processedBy', function () {
                return [
                    'id' => $this->processedBy->id,
                    'name' => $this->processedBy->name,
                ];
            }),
            'savingsGoal' => $this->whenLoaded('savingsGoal', function () {
                return [
                    'id' => $this->savingsGoal->id,
                    'name' => $this->savingsGoal->name,
                ];
            }),
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}

