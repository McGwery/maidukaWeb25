<?php

namespace App\Http\Requests\PurchaseOrder;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePurchaseOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(PurchaseOrderStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}