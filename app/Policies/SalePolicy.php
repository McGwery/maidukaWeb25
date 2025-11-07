<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class SalePolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any sales.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_sales');
    }

    /**
     * Determine if the user can view the sale.
     */
    public function view(User $user, Sale $sale): bool
    {
        return $this->hasPermission($user, $sale->shop, 'view_sales');
    }

    /**
     * Determine if the user can create sales (process sales).
     */
    public function create(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'process_sales');
    }

    /**
     * Determine if the user can update the sale.
     */
    public function update(User $user, Sale $sale): bool
    {
        return $this->hasPermission($user, $sale->shop, 'manage_sales');
    }

    /**
     * Determine if the user can delete the sale.
     */
    public function delete(User $user, Sale $sale): bool
    {
        return $this->isOwner($user, $sale->shop);
    }

    /**
     * Determine if the user can refund sales.
     */
    public function refund(User $user, Sale $sale): bool
    {
        return $this->hasPermission($user, $sale->shop, 'refund_sales') ||
               $this->hasPermission($user, $sale->shop, 'manage_sales');
    }

    /**
     * Determine if the user can view sales analytics.
     */
    public function viewAnalytics(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_sales_analytics') ||
               $this->hasPermission($user, $shop, 'view_sales');
    }

    /**
     * Determine if the user can add payments to sale.
     */
    public function addPayment(User $user, Sale $sale): bool
    {
        return $this->hasPermission($user, $sale->shop, 'process_sales') ||
               $this->hasPermission($user, $sale->shop, 'manage_sales');
    }
}

