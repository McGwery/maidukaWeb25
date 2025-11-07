<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class CustomerPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any customers.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_customers');
    }

    /**
     * Determine if the user can view the customer.
     */
    public function view(User $user, Customer $customer): bool
    {
        return $this->hasPermission($user, $customer->shop, 'view_customers');
    }

    /**
     * Determine if the user can create customers.
     */
    public function create(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_customers');
    }

    /**
     * Determine if the user can update the customer.
     */
    public function update(User $user, Customer $customer): bool
    {
        return $this->hasPermission($user, $customer->shop, 'manage_customers');
    }

    /**
     * Determine if the user can delete the customer.
     */
    public function delete(User $user, Customer $customer): bool
    {
        return $this->hasPermission($user, $customer->shop, 'manage_customers');
    }
}

