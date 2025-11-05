<?php

namespace App\Http\Requests;

use App\Enums\ExpenseCategory;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => ['sometimes', 'required', 'string', Rule::in(ExpenseCategory::values())],
            'amount' => 'sometimes|required|numeric|min:0',
            'expenseDate' => 'sometimes|required|date',
            'paymentMethod' => ['sometimes', 'required', 'string', Rule::in(PaymentMethod::values())],
            'receiptNumber' => 'nullable|string|max:100',
            'attachmentUrl' => 'nullable|url|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Expense title is required.',
            'category.required' => 'Expense category is required.',
            'category.in' => 'Invalid expense category.',
            'amount.required' => 'Expense amount is required.',
            'amount.min' => 'Expense amount cannot be negative.',
            'expenseDate.required' => 'Expense date is required.',
            'paymentMethod.required' => 'Payment method is required.',
            'paymentMethod.in' => 'Invalid payment method.',
        ];
    }
}

