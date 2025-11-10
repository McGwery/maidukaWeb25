<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'creditLimit' => $this->credit_limit,
            'currentDebt' => $this->current_debt,
            'totalPurchases' => $this->total_purchases,
            'totalPaid' => $this->total_paid,
            'availableCredit' => $this->credit_limit - $this->current_debt,
            'notes' => $this->notes,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

