<?php

namespace App\Http\Requests;

use App\Enums\ShopMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ShopMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-members', $this->route('shop'));
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', new Enum(ShopMemberRole::class)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ];
    }
}