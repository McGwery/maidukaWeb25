<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class CreateShopRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:retail,cafe,restaurant,service,other'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'is_online' => ['sometimes', 'boolean'],
            'address' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'email' => ['sometimes', 'email', 'max:255'],
            'description' => ['sometimes', 'string'],
            'business_hours' => ['sometimes', 'array'],
            'business_hours.*.day' => ['required_with:business_hours', 'integer', 'between:0,6'],
            'business_hours.*.open' => ['required_with:business_hours', 'date_format:H:i'],
            'business_hours.*.close' => ['required_with:business_hours', 'date_format:H:i'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.in' => 'The shop type must be one of: retail, cafe, restaurant, service, or other',
            'status.in' => 'The shop status must be one of: active, inactive, or suspended',
            'business_hours.*.day.between' => 'The day must be between 0 (Sunday) and 6 (Saturday)',
            'business_hours.*.open.date_format' => 'The opening time must be in 24-hour format (HH:mm)',
            'business_hours.*.close.date_format' => 'The closing time must be in 24-hour format (HH:mm)',
        ];
    }
}