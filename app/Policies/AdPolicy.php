<?php

namespace App\Policies;

use App\Models\Ad;
use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class AdPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any ads.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_ads');
    }

    /**
     * Determine if the user can view the ad.
     */
    public function view(User $user, Ad $ad): bool
    {
        if ($ad->shop_id) {
            return $this->hasPermission($user, $ad->shop, 'view_ads');
        }
        // Admin ads are viewable by all
        return true;
    }

    /**
     * Determine if the user can create ads.
     */
    public function create(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_ads');
    }

    /**
     * Determine if the user can update the ad.
     */
    public function update(User $user, Ad $ad): bool
    {
        if (!$ad->shop_id) {
            // Only system admin can update admin ads
            return false;
        }
        return $this->hasPermission($user, $ad->shop, 'manage_ads');
    }

    /**
     * Determine if the user can delete the ad.
     */
    public function delete(User $user, Ad $ad): bool
    {
        if (!$ad->shop_id) {
            return false;
        }
        return $this->hasPermission($user, $ad->shop, 'manage_ads');
    }

    /**
     * Determine if the user can view ad analytics.
     */
    public function viewAnalytics(User $user, Ad $ad): bool
    {
        if (!$ad->shop_id) {
            return false;
        }
        return $this->hasPermission($user, $ad->shop, 'view_ads');
    }

    /**
     * Determine if the user can approve ads (admin only).
     */
    public function approve(User $user): bool
    {
        // This would need system admin check
        // For now, return false or check against admin table
        return false;
    }

    /**
     * Determine if the user can reject ads (admin only).
     */
    public function reject(User $user): bool
    {
        return false;
    }
}

