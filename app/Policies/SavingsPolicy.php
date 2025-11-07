<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class SavingsPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view savings.
     */
    public function view(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_savings');
    }

    /**
     * Determine if the user can manage savings settings.
     */
    public function manageSettings(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_savings');
    }

    /**
     * Determine if the user can deposit to savings.
     */
    public function deposit(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_savings');
    }

    /**
     * Determine if the user can withdraw from savings.
     */
    public function withdraw(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_savings');
    }

    /**
     * Determine if the user can view savings transactions.
     */
    public function viewTransactions(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_savings');
    }

    /**
     * Determine if the user can manage goals.
     */
    public function manageGoals(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_savings');
    }
}

