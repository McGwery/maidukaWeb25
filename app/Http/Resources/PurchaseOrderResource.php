<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'referenceNumber' => $this->reference_number,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'totalAmount' => $this->total_amount,
            'totalPaid' => $this->total_paid,
            'remainingBalance' => $this->remaining_balance,
            'isFullyPaid' => $this->isFullyPaid(),
            'notes' => $this->notes,
            'approvedAt' => $this->approved_at?->toIso8601String(),
            
            // Relationships
            'buyerShop' => new ShopResource($this->whenLoaded('buyerShop')),
            'sellerShop' => new ShopResource($this->whenLoaded('sellerShop')),
            'approvedBy' => new UserResource($this->whenLoaded('approvedBy')),
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PurchasePaymentResource::collection($this->whenLoaded('payments')),
            'stockTransfers' => StockTransferResource::collection($this->whenLoaded('stockTransfers')),
            
            // Counts
            'itemsCount' => $this->whenCounted('items'),
            'paymentsCount' => $this->whenCounted('payments'),
            
            // Timestamps
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
        ];
    }
}