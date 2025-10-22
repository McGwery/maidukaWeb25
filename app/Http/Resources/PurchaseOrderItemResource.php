<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unit_price,
            'totalPrice' => $this->total_price,
            'notes' => $this->notes,
            
            // Relationships
            'product' => new ProductResource($this->whenLoaded('product')),
            
            // Timestamps
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}