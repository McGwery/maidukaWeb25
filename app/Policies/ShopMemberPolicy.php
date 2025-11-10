<?php

namespace App\Policies;

use App\Models\ShopMember;
use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class ShopMemberPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any shop members.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_employees');
    }

    /**
     * Determine if the user can view the shop member.
     */
    public function view(User $user, ShopMember $shopMember): bool
    {
        return $this->hasPermission($user, $shopMember->shop, 'view_employees');
    }

    /**
     * Determine if the user can create shop members.
     */
    public function create(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_employees');
    }

    /**
     * Determine if the user can update the shop member.
     */
    public function update(User $user, ShopMember $shopMember): bool
    {
        return $this->hasPermission($user, $shopMember->shop, 'manage_employees');
    }

    /**
     * Determine if the user can delete the shop member.
     */
    public function delete(User $user, ShopMember $shopMember): bool
    {
        return $this->hasPermission($user, $shopMember->shop, 'manage_employees');
    }
}

