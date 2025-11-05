<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|uuid|exists:products,id',
            'items.*.name' => 'required|string',
            'items.*.originalPrice' => 'required|numeric|min:0',
            'items.*.currentPrice' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.total' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string',
            'items.*.allowFractional' => 'nullable|boolean',
            'items.*.negotiable' => 'nullable|boolean',

            'customer' => 'nullable|array',
            'customer.id' => 'nullable|uuid|exists:customers,id',
            'customer.name' => 'required_with:customer|string|max:255',
            'customer.phone' => 'nullable|string|max:20',
            'customer.debt' => 'nullable|numeric',

            'paymentMethod' => ['required', Rule::enum(PaymentMethod::class)],
            'total' => 'required|numeric|min:0',
            'amountReceived' => 'required|numeric|min:0',
            'change' => 'nullable|numeric|min:0',

            'taxRate' => 'nullable|numeric|min:0|max:100',
            'discountAmount' => 'nullable|numeric|min:0',
            'discountPercentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required to complete the sale.',
            'items.min' => 'At least one item is required to complete the sale.',
            'items.*.id.exists' => 'One or more products no longer exist.',
            'paymentMethod.required' => 'Please select a payment method.',
            'amountReceived.min' => 'Amount received must be greater than or equal to 0.',
        ];
    }
}

