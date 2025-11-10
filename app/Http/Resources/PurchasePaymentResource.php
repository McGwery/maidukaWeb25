<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'paymentMethod' => [
                'value' => $this->payment_method->value,
                'label' => $this->payment_method->label(),
            ],
            'referenceNumber' => $this->reference_number,
            'notes' => $this->notes,
            
            // Relationships
            'recordedBy' => new UserResource($this->whenLoaded('recordedBy')),
            
            // Timestamps
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}