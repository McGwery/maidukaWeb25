<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'productName' => $this->product?->product_name,
            'userId' => $this->user_id,
            'userName' => $this->user?->name,
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
            'quantity' => $this->quantity,
            'valueAtTime' => $this->value_at_time,
            'monetaryImpact' => $this->getMonetaryImpact(),
            'previousStock' => $this->previous_stock,
            'newStock' => $this->new_stock,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'isReduction' => $this->isReduction(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

