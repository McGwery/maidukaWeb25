<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'transferredAt' => $this->transferred_at?->toIso8601String(),
            'notes' => $this->notes,
            
            // Relationships
            'product' => new ProductResource($this->whenLoaded('product')),
            'transferredBy' => new UserResource($this->whenLoaded('transferredBy')),
            
            // Timestamps
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}