<?php

namespace App\Http\Requests\Shop;

use App\Enums\Currency;
use App\Enums\ShopType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', new Enum(ShopType::class)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'agent_code' => ['nullable', 'string'],
            'currency' => ['required', new Enum(Currency::class)],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('agent_code')) {
            $this->merge([
                'agent_code' => strtoupper($this->agent_code),
            ]);
        }
    }
}
