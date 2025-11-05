<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => [
                'value' => $this->category->value,
                'label' => $this->category->label(),
            ],
            'amount' => $this->amount,
            'expenseDate' => $this->expense_date->format('Y-m-d'),
            'paymentMethod' => [
                'value' => $this->payment_method->value,
                'label' => $this->payment_method->label(),
            ],
            'receiptNumber' => $this->receipt_number,
            'attachmentUrl' => $this->attachment_url,
            'recordedBy' => [
                'id' => $this->recordedBy->id,
                'name' => $this->recordedBy->name,
            ],
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

