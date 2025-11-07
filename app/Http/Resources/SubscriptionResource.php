<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,
            'plan' => [
                'value' => $this->plan->value,
                'label' => $this->plan->label(),
                'price' => $this->plan->price(),
                'durationDays' => $this->plan->durationDays(),
                'features' => $this->plan->features(),
            ],
            'type' => [
                'value' => $this->type->value,
                'label' => $this->type->label(),
                'description' => $this->type->description(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'price' => $this->price,
            'currency' => [
                'value' => $this->currency->value,
                'label' => $this->currency->label(),
                'symbol' => $this->currency->symbol(),
            ],
            'startsAt' => $this->starts_at?->toIso8601String(),
            'expiresAt' => $this->expires_at?->toIso8601String(),
            'autoRenew' => $this->auto_renew,
            'paymentMethod' => $this->payment_method,
            'transactionReference' => $this->transaction_reference,
            'features' => $this->features,
            'maxUsers' => $this->max_users,
            'maxProducts' => $this->max_products,
            'notes' => $this->notes,
            'cancelledAt' => $this->cancelled_at?->toIso8601String(),
            'cancelledReason' => $this->cancelled_reason,
            'isActive' => $this->isActive(),
            'isExpired' => $this->isExpired(),
            'isExpiringSoon' => $this->isExpiringSoon(),
            'daysRemaining' => $this->daysRemaining(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

