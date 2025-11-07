<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenewSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'durationDays' => 'nullable|integer|min:1|max:365',
            'paymentMethod' => 'nullable|string',
            'transactionReference' => 'nullable|string',
        ];
    }
}
