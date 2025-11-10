<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'targetAmount' => 'nullable|numeric|min:1',
            'targetDate' => 'nullable|date',
            'status' => 'nullable|in:active,completed,cancelled,paused',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'priority' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'targetAmount.min' => 'Target amount must be at least 1.',
            'status.in' => 'Invalid status. Must be active, completed, cancelled, or paused.',
            'priority.min' => 'Priority cannot be negative.',
        ];
    }
}

