<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'saleId' => $this->sale_id,
            'userId' => $this->user_id,
            'userName' => $this->user?->name,
            'paymentMethod' => $this->payment_method->value,
            'paymentMethodLabel' => $this->payment_method->label(),
            'amount' => $this->amount,
            'referenceNumber' => $this->reference_number,
            'notes' => $this->notes,
            'paymentDate' => $this->payment_date,
            'createdAt' => $this->created_at,
        ];
    }
}

