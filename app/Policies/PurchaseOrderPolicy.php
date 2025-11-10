<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class PurchaseOrderPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any purchase orders.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_purchases');
    }

    /**
     * Determine if the user can view the purchase order.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Can view if it's their shop as buyer or seller
        return $this->hasPermission($user, $purchaseOrder->buyerShop, 'view_purchases') ||
               $this->hasPermission($user, $purchaseOrder->sellerShop, 'view_purchases');
    }

    /**
     * Determine if the user can create purchase orders.
     */
    public function create(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_purchases');
    }

    /**
     * Determine if the user can update the purchase order.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->hasPermission($user, $purchaseOrder->buyerShop, 'manage_purchases');
    }

    /**
     * Determine if the user can delete the purchase order.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->hasPermission($user, $purchaseOrder->buyerShop, 'manage_purchases');
    }

    /**
     * Determine if the user can update purchase order status.
     */
    public function updateStatus(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Buyer can update, seller needs approve permission
        if ($purchaseOrder->buyer_shop_id === $purchaseOrder->buyerShop->id) {
            return $this->hasPermission($user, $purchaseOrder->buyerShop, 'manage_purchases');
        }

        return $this->hasPermission($user, $purchaseOrder->sellerShop, 'approve_purchases');
    }

    /**
     * Determine if the user can record payments.
     */
    public function recordPayment(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->hasPermission($user, $purchaseOrder->buyerShop, 'record_purchase_payments') ||
               $this->hasPermission($user, $purchaseOrder->buyerShop, 'manage_purchases');
    }

    /**
     * Determine if the user can transfer stock.
     */
    public function transferStock(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->hasPermission($user, $purchaseOrder->buyerShop, 'transfer_stock') ||
               $this->hasPermission($user, $purchaseOrder->buyerShop, 'manage_purchases');
    }
}

