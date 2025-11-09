<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,
            'shop' => $this->when($this->relationLoaded('shop') && $this->shop, function() {
                return [
                    'id' => $this->shop->id,
                    'name' => $this->shop->name,
                    'imageUrl' => $this->shop->image_url,
                ];
            }),

            // Content
            'title' => $this->title,
            'description' => $this->description,
            'imageUrl' => $this->image_url,
            'videoUrl' => $this->video_url,
            'mediaType' => $this->media_type,
            'ctaText' => $this->cta_text,
            'ctaUrl' => $this->cta_url,

            // Targeting
            'targetCategories' => $this->target_categories,
            'targetShopTypes' => $this->target_shop_types,
            'targetLocation' => $this->target_location,
            'targetAll' => $this->target_all,

            // Ad settings
            'adType' => [
                'value' => $this->ad_type->value,
                'label' => $this->ad_type->label(),
                'description' => $this->ad_type->description(),
            ],
            'placement' => [
                'value' => $this->placement->value,
                'label' => $this->placement->label(),
            ],
            'priority' => $this->priority,

            // Scheduling
            'startsAt' => $this->starts_at?->toIso8601String(),
            'expiresAt' => $this->expires_at?->toIso8601String(),
            'isActive' => $this->is_active,
            'isLive' => $this->isLive(),
            'isExpired' => $this->isExpired(),
            'isScheduled' => $this->isScheduled(),

            // Budget
            'budget' => $this->budget,
            'costPerClick' => $this->cost_per_click,
            'totalSpent' => $this->total_spent,
            'remainingBudget' => $this->budget ? $this->budget - $this->total_spent : null,

            // Analytics
            'analytics' => [
                'viewCount' => $this->view_count,
                'clickCount' => $this->click_count,
                'uniqueViewCount' => $this->unique_view_count,
                'uniqueClickCount' => $this->unique_click_count,
                'ctr' => $this->ctr,
                'engagementRate' => $this->view_count > 0
                    ? round(($this->click_count / $this->view_count) * 100, 2)
                    : 0,
            ],

            // Status
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'rejectionReason' => $this->rejection_reason,

            // Admin info
            'createdBy' => $this->when($this->created_by, function() {
                return $this->creator ? [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ] : null;
            }),
            'approvedBy' => $this->when($this->approved_by, function() {
                return $this->approver ? [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ] : null;
            }),
            'approvedAt' => $this->approved_at?->toIso8601String(),

            // Meta
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

