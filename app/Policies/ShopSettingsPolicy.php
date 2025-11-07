<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\ShopSettings;
use App\Models\User;
use App\Traits\HasShopPolicy;

class ShopSettingsPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view shop settings.
     */
    public function view(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_settings');
    }

    /**
     * Determine if the user can update shop settings.
     */
    public function update(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_settings');
    }

    /**
     * Determine if the user can reset shop settings.
     */
    public function reset(User $user, Shop $shop): bool
    {
        return $this->isOwner($user, $shop);
    }
}

