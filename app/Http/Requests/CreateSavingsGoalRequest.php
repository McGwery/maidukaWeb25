<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'targetAmount' => 'required|numeric|min:1',
            'targetDate' => 'nullable|date|after:today',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'priority' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Goal name is required.',
            'targetAmount.required' => 'Target amount is required.',
            'targetAmount.min' => 'Target amount must be at least 1.',
            'targetDate.after' => 'Target date must be in the future.',
            'priority.min' => 'Priority cannot be negative.',
        ];
    }
}

