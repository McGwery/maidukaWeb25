<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'imageUrl' => 'nullable|url',
            'videoUrl' => 'nullable|url',
            'mediaType' => 'nullable|in:image,video',
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
            'adType' => 'nullable|in:banner,card,popup,native',
            'placement' => 'nullable|in:home,products,sales,reports,all',
            'priority' => 'nullable|integer|min:0|max:100',
            'isActive' => 'nullable|boolean',

            // Scheduling
            'startsAt' => 'nullable|date',
            'expiresAt' => 'nullable|date|after:startsAt',

            // Budget
            'budget' => 'nullable|numeric|min:0',
            'costPerClick' => 'nullable|numeric|min:0',

            // Notes
            'notes' => 'nullable|string|max:500',
        ];
    }
}

