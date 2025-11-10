<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $activeShop = $this->activeShop?->shop;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'isPhoneVerified' => !is_null($this->phone_verified_at),
            'isPhoneLoginEnabled' => $this->is_phone_login_enabled,
            'activeShop' => $activeShop ? new ShopResource($activeShop) : null,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
