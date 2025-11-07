<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSavingsSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Adjust authorization logic as needed (e.g. check shop ownership/permissions)
        return true;
    }

    /**
     * Prepare the data for validation.
     * Cast common incoming values to the expected types.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'proposed_amount' => $this->whenFilled('proposed_amount', fn ($v) => is_numeric($v) ? (float) $v : $v),
            'proposed_percentage' => $this->whenFilled('proposed_percentage', fn ($v) => is_numeric($v) ? (float) $v : $v),
            'enabled' => $this->boolean('enabled'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Common fields included:
     * - proposed_amount: numeric, >= 0
     * - proposed_percentage: numeric between 0 and 100
     * - start_date / end_date: valid dates, end_date >= start_date
     * - frequency: optional set of allowed values
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Optionally scope by shop: 'shop_id' => ['required', 'integer', 'exists:shops,id'],
            'proposed_amount' => ['nullable', 'numeric', 'min:0'],
            'proposed_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'saving_goal' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'frequency' => ['nullable', 'in:daily,weekly,monthly,yearly'],
            'enabled' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom messages (optional) for clearer API responses.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proposed_percentage.between' => 'Proposed percentage must be between 0 and 100.',
            'end_date.after_or_equal' => 'End date must be the same as or after the start date.',
            'frequency.in' => 'Frequency must be one of: daily, weekly, monthly, yearly.',
        ];
    }
}
