<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,
            'customerId' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', fn() => new CustomerResource($this->customer)),
            'userId' => $this->user_id,
            'userName' => $this->user?->name,
            'saleNumber' => $this->sale_number,
            'subtotal' => $this->subtotal,
            'taxRate' => $this->tax_rate,
            'taxAmount' => $this->tax_amount,
            'discountAmount' => $this->discount_amount,
            'discountPercentage' => $this->discount_percentage,
            'totalAmount' => $this->total_amount,
            'amountPaid' => $this->amount_paid,
            'changeAmount' => $this->change_amount,
            'debtAmount' => $this->debt_amount,
            'profitAmount' => $this->profit_amount,
            'status' => $this->status->value,
            'statusLabel' => $this->status->label(),
            'statusColor' => $this->status->color(),
            'paymentStatus' => $this->payment_status,
            'notes' => $this->notes,
            'saleDate' => $this->sale_date,
            'items' => $this->whenLoaded('items', fn() => SaleItemResource::collection($this->items)),
            'payments' => $this->whenLoaded('payments', fn() => SalePaymentResource::collection($this->payments)),
            'itemsCount' => $this->items_count ?? $this->items()->count(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

