<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackAdViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shopId' => 'nullable|uuid|exists:shops,id',
            'deviceType' => 'nullable|in:mobile,tablet,desktop',
            'platform' => 'nullable|in:android,ios,web',
            'viewDuration' => 'nullable|integer|min:0',
        ];
    }
}

