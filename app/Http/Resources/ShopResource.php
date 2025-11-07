<?php

namespace App\Http\Resources;

use App\Enums\Currency;
use App\Enums\ShopType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'businessType' => [
                'value' => $this->business_type?->value,
                'label' => $this->business_type?->label(),
            ],
            'phoneNumber' => $this->phone_number,
            'address' => $this->address,
            'agentCode' => $this->agent_code,
            'currency' => [
                'code' => $this->currency?->value,
                'symbol' => $this->currency?->symbol(),
                'label' => $this->currency?->label(),
            ],
            'imageUrl' => $this->image_url,
            'isActive' => $this->is_active,
            'isCurrentSelected' => $this->activeShop()->exists(),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'members' => ShopMemberResource::collection($this->whenLoaded('members')),
            'membersCount' => $this->whenCounted('members'),

            // Include settings summary if loaded
            'settings' => $this->when($this->relationLoaded('settings'), function() {
                $settings = $this->settings;
                return $settings ? [
                    'language' => $settings->language,
                    'timezone' => $settings->timezone,
                    'isCurrentlyOpen' => $settings->isCurrentlyOpen(),
                    'allowCreditSales' => $settings->allow_credit_sales,
                    'allowDiscounts' => $settings->allow_discounts,
                    'trackStock' => $settings->track_stock,
                ] : null;
            }),

            // Include active subscription if loaded
            'activeSubscription' => $this->when($this->relationLoaded('activeSubscription'), function() {
                return $this->activeSubscription ? [
                    'id' => $this->activeSubscription->id,
                    'plan' => $this->activeSubscription->plan->value,
                    'planLabel' => $this->activeSubscription->plan->label(),
                    'type' => $this->activeSubscription->type->value,
                    'expiresAt' => $this->activeSubscription->expires_at?->toIso8601String(),
                    'daysRemaining' => $this->activeSubscription->daysRemaining(),
                    'isExpiringSoon' => $this->activeSubscription->isExpiringSoon(),
                ] : null;
            }),

            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
