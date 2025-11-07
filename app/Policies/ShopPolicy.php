<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class ShopPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any shops.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own shops
    }

    /**
     * Determine if the user can view the shop.
     */
    public function view(User $user, Shop $shop): bool
    {
        return $this->canAccessShop($user, $shop);
    }

    /**
     * Determine if the user can create shops.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a shop
    }

    /**
     * Determine if the user can update the shop.
     */
    public function update(User $user, Shop $shop): bool
    {
        return $this->isOwner($user, $shop) ||
               $this->hasPermission($user, $shop, 'manage_settings');
    }

    /**
     * Determine if the user can delete the shop.
     */
    public function delete(User $user, Shop $shop): bool
    {
        return $this->isOwner($user, $shop);
    }

    /**
     * Determine if the user can switch to this shop.
     */
    public function switch(User $user, Shop $shop): bool
    {
        return $this->canAccessShop($user, $shop);
    }

    /**
     * Determine if the user can set shop as active.
     */
    public function setActive(User $user, Shop $shop): bool
    {
        return $this->canAccessShop($user, $shop);
    }
}

