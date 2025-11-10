<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentShopId = $request->route('shop');
        $otherShop = $this->getOtherShop($currentShopId);

        return [
            'id' => $this->id,
            'otherShop' => [
                'id' => $otherShop?->id,
                'name' => $otherShop?->name,
                'shopType' => $otherShop?->shop_type,
                'logoUrl' => $otherShop?->logo_url,
                'location' => $otherShop?->location,
            ],
            'lastMessage' => $this->last_message,
            'lastMessageAt' => $this->last_message_at?->toIso8601String(),
            'lastMessageBy' => [
                'id' => $this->lastMessageUser?->id,
                'name' => $this->lastMessageUser?->name,
            ],
            'unreadCount' => $this->getUnreadCount($currentShopId),
            'isArchived' => $this->isArchived($currentShopId),
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}

