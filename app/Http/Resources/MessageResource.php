<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentShopId = $request->route('shop') ?? $request->input('shopId');
        $isSender = $this->sender_shop_id === $currentShopId;

        return [
            'id' => $this->id,
            'conversationId' => $this->conversation_id,
            'message' => $this->message,
            'messageType' => [
                'value' => $this->message_type->value,
                'label' => $this->message_type->label(),
                'icon' => $this->message_type->icon(),
            ],
            'attachments' => $this->attachments,
            'product' => $this->when($this->product_id, function () {
                return [
                    'id' => $this->product?->id,
                    'name' => $this->product?->name,
                    'price' => $this->product?->selling_price,
                    'imageUrl' => $this->product?->image_url,
                    'inStock' => $this->product?->quantity > 0,
                ];
            }),
            'location' => $this->when($this->location_lat && $this->location_lng, [
                'lat' => $this->location_lat,
                'lng' => $this->location_lng,
                'name' => $this->location_name,
            ]),
            'sender' => [
                'shopId' => $this->sender_shop_id,
                'shopName' => $this->senderShop->name,
                'userId' => $this->sender_user_id,
                'userName' => $this->senderUser->name,
            ],
            'receiver' => [
                'shopId' => $this->receiver_shop_id,
                'shopName' => $this->receiverShop->name,
            ],
            'isSender' => $isSender,
            'isRead' => $this->is_read,
            'readAt' => $this->read_at?->toIso8601String(),
            'isDelivered' => $this->is_delivered,
            'deliveredAt' => $this->delivered_at?->toIso8601String(),
            'replyTo' => $this->when($this->reply_to_message_id, function () {
                return [
                    'id' => $this->replyTo?->id,
                    'message' => $this->replyTo?->message,
                    'senderName' => $this->replyTo?->senderUser->name,
                ];
            }),
            'reactions' => $this->reactions->groupBy('reaction')->map(function ($group) {
                return [
                    'reaction' => $group->first()->reaction,
                    'count' => $group->count(),
                    'users' => $group->map(fn($r) => [
                        'id' => $r->user->id,
                        'name' => $r->user->name,
                    ]),
                ];
            })->values(),
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}

