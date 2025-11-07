<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'imageUrl' => 'required_without:videoUrl|nullable|url',
            'videoUrl' => 'required_without:imageUrl|nullable|url',
            'mediaType' => 'required|in:image,video',
            'ctaText' => 'nullable|string|max:50',
            'ctaUrl' => 'nullable|url',

            // Targeting
            'targetCategories' => 'nullable|array',
            'targetCategories.*' => 'uuid|exists:categories,id',
            'targetShopTypes' => 'nullable|array',
            'targetShopTypes.*' => 'string',
            'targetLocation' => 'nullable|string|max:100',
            'targetAll' => 'nullable|boolean',

            // Ad settings
            'adType' => 'required|in:banner,card,popup,native',
            'placement' => 'required|in:home,products,sales,reports,all',
            'priority' => 'nullable|integer|min:0|max:100',

            // Scheduling
            'startsAt' => 'required|date|after_or_equal:today',
            'expiresAt' => 'required|date|after:startsAt',

            // Budget
            'budget' => 'nullable|numeric|min:0',
            'costPerClick' => 'nullable|numeric|min:0',

            // Notes
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Ad title is required',
            'imageUrl.required_without' => 'Either image or video is required',
            'videoUrl.required_without' => 'Either image or video is required',
            'expiresAt.after' => 'Expiry date must be after start date',
        ];
    }
}

