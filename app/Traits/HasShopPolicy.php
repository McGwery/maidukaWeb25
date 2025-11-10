<?php

namespace App\Traits;

use App\Models\Shop;
use App\Models\User;

trait HasShopPolicy
{
    /**
     * Check if user has permission in the shop
     */
    protected function hasPermission(User $user, Shop $shop, string $permission): bool
    {
        // Owner has all permissions
        if ($shop->isOwner($user)) {
            return true;
        }

        // Check if user is an active member with the permission
        $member = $shop->members()->where('user_id', $user->id)->first();

        if (!$member || !$member->pivot->is_active) {
            return false;
        }

        $memberPermissions = $member->pivot->permissions ?? [];

        // Check if has wildcard permission
        if (in_array('*', $memberPermissions)) {
            return true;
        }

        // Check specific permission
        return in_array($permission, $memberPermissions);
    }

    /**
     * Check if user can access the shop
     */
    protected function canAccessShop(User $user, Shop $shop): bool
    {
        return $shop->isOwner($user) ||
               $shop->members()->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Check if user is shop owner
     */
    protected function isOwner(User $user, Shop $shop): bool
    {
        return $shop->isOwner($user);
    }

    /**
     * Check if user has any of the given permissions
     */
    protected function hasAnyPermission(User $user, Shop $shop, array $permissions): bool
    {
        if ($shop->isOwner($user)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $shop, $permission)) {
                return true;
            }
        }

        return false;
    }
}

