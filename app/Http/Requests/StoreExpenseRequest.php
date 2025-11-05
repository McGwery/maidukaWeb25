<?php

namespace App\Http\Requests;

use App\Enums\ExpenseCategory;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => ['required', 'string', Rule::in(ExpenseCategory::values())],
            'amount' => 'required|numeric|min:0',
            'expenseDate' => 'required|date',
            'paymentMethod' => ['required', 'string', Rule::in(PaymentMethod::values())],
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

