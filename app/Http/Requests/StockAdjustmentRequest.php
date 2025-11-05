<?php

namespace App\Http\Requests;

use App\Enums\StockAdjustmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(StockAdjustmentType::class)],
            'quantity' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please specify the type of stock adjustment.',
            'quantity.required' => 'Please specify the quantity to adjust.',
            'quantity.not_in' => 'Quantity cannot be zero.',
            'reason.required' => 'Please provide a reason for this adjustment.',
        ];
    }
}

