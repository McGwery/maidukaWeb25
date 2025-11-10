<?php

namespace App\Http\Requests;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
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
            'plan' => ['nullable', 'string', Rule::in(array_column(SubscriptionPlan::cases(), 'value'))],
            'type' => ['nullable', 'string', Rule::in(array_column(SubscriptionType::cases(), 'value'))],
            'status' => ['nullable', 'string', Rule::in(array_column(SubscriptionStatus::cases(), 'value'))],
            'autoRenew' => 'nullable|boolean',
            'paymentMethod' => 'nullable|string',
            'transactionReference' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}

