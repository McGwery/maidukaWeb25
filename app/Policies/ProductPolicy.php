<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class ProductPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any products.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_products') ||
               $this->hasPermission($user, $shop, 'view_inventory');
    }

    /**
     * Determine if the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        return $this->hasPermission($user, $product->shop, 'view_products') ||
               $this->hasPermission($user, $product->shop, 'view_inventory');
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_products') ||
               $this->hasPermission($user, $shop, 'manage_inventory');
    }

    /**
     * Determine if the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return $this->hasPermission($user, $product->shop, 'manage_products') ||
               $this->hasPermission($user, $product->shop, 'manage_inventory');
    }

    /**
     * Determine if the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        return $this->hasPermission($user, $product->shop, 'manage_products') ||
               $this->hasPermission($user, $product->shop, 'manage_inventory');
    }

    /**
     * Determine if the user can update stock.
     */
    public function updateStock(User $user, Product $product): bool
    {
        return $this->hasPermission($user, $product->shop, 'update_stock') ||
               $this->hasPermission($user, $product->shop, 'manage_inventory');
    }

    /**
     * Determine if the user can view stock adjustments.
     */
    public function viewStockAdjustments(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_stock_adjustments') ||
               $this->hasPermission($user, $shop, 'view_inventory');
    }

    /**
     * Determine if the user can view inventory analysis.
     */
    public function viewInventoryAnalysis(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_inventory_analysis') ||
               $this->hasPermission($user, $shop, 'view_inventory');
    }
}

