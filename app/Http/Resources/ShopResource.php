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
            'owner' => new UserResource($this->whenLoaded('owner')),
            'members' => ShopMemberResource::collection($this->whenLoaded('members')),
            'membersCount' => $this->whenCounted('members'),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
        ];
    }
}