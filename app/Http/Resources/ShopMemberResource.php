<?php

namespace App\Http\Resources;

use App\Enums\ShopMemberRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role = ShopMemberRole::from($this->role);

        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'role' => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            'permissions' => $this->permissions,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}