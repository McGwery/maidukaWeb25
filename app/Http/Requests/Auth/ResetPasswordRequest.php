<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
        return match ($this->route()->getName()) {
            'auth.password.reset.request' => [
                'phone' => ['required', 'string', 'exists:users,phone'],
            ],
            'auth.password.reset' => [
                'phone' => ['required', 'string', 'exists:users,phone'],
                'otp' => ['required', 'string', 'size:6'],
                'password' => ['required', 'string', 'min:8'],
            ],
            default => []
        };
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.exists' => 'No account found with this phone number.',
            'otp.size' => 'The OTP must be exactly 6 digits.',
            'password.min' => 'The new password must be at least 8 characters long.',
        ];
    }
}