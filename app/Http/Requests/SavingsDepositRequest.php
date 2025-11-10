<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavingsDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'savingsGoalId' => 'nullable|uuid|exists:savings_goals,id',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Deposit amount is required.',
            'amount.min' => 'Deposit amount must be at least 0.01.',
            'savingsGoalId.exists' => 'The selected savings goal does not exist.',
        ];
    }
}

