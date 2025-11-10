<?php

namespace App\Http\Requests\Shop;

class UpdateShopRequest extends CreateShopRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_map(function ($rule) {
            // Make all fields optional for updates
            return array_merge(['sometimes'], is_array($rule) ? $rule : [$rule]);
        }, parent::rules());
    }
}