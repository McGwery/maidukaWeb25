<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopId' => $this->shop_id,
            'name' => $this->name,
            'description' => $this->description,
            'targetAmount' => (float) $this->target_amount,
            'currentAmount' => (float) $this->current_amount,
            'targetDate' => $this->target_date,
            'icon' => $this->icon,
            'color' => $this->color,
            'priority' => $this->priority,
            'status' => $this->status,
            'progressPercentage' => $this->progress_percentage,
            'isCompleted' => $this->status === 'completed',
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}

