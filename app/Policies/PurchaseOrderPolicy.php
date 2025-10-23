<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Shop;
use App\Enums\ShopMemberRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view purchase orders.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        $member = $shop->shopMembers()->where('user_id', $user->id)->first();
        if (!$member || !$member->is_active) return false;

        return in_array('*', $member->permissions) ||
               in_array('manage_purchases', $member->permissions) ||
               in_array('view_inventory', $member->permissions);
    }

    /**
     * Determine whether the user can view the purchase order.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder, Shop $shop): bool
    {
        if ($purchaseOrder->buyer_shop_id !== $shop->id && $purchaseOrder->seller_shop_id !== $shop->id) {
            return false;
        }

        $member = $shop->shopMembers()->where('user_id', $user->id)->first();
        if (!$member || !$member->is_active) return false;

        return in_array('*', $member->permissions) ||
               in_array('manage_purchases', $member->permissions) ||
               in_array('view_inventory', $member->permissions);
    }

    /**
     * Determine whether the user can create purchase orders.
     */
    public function create(User $user, Shop $shop): bool
    {
        $member = $shop->shopMembers()->where('user_id', $user->id)->first();
        if (!$member || !$member->is_active) return false;

        return in_array('*', $member->permissions) ||
               in_array('manage_purchases', $member->permissions);
    }

    /**
     * Determine whether the user can update the purchase order.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder, Shop $shop): bool
    {
        if ($purchaseOrder->buyer_shop_id !== $shop->id) {
            return false;
        }

        $member = $shop->shopMembers()->where('user_id', $user->id)->first();
        if (!$member || !$member->is_active) return false;

        return in_array('*', $member->permissions) ||
               in_array('manage_purchases', $member->permissions);
    }

    /**
     * Determine whether the user can approve the purchase order.
     */
    public function approve(User $user, PurchaseOrder $purchaseOrder, Shop $shop): bool
    {
        if ($purchaseOrder->buyer_shop_id !== $shop->id) {
            return false;
        }

        $member = $shop->shopMembers()->where('user_id', $user->id)->first();
        if (!$member || !$member->is_active) return false;

        return in_array('*', $member->permissions) ||
               in_array('approve_purchases', $member->permissions);
    }

    /**
     * Determine whether the user can record payments.
     */
    public function recordPayment(User $user, PurchaseOrder $purchaseOrder, Shop $shop): bool
    {
        if ($purchaseOrder->buyer_shop_id !== $shop->id) {
            return false;
        }

        $member = $shop->shopMembers()->where('user_id', $user->id)->first();
        if (!$member || !$member->is_active) return false;

        return in_array('*', $member->permissions) ||
               in_array('record_purchase_payments', $member->permissions);
    }

    /**
     * Determine whether the user can transfer stock.
     */
    public function transferStock(User $user, PurchaseOrder $purchaseOrder, Shop $shop): bool
    {
        if ($purchaseOrder->seller_shop_id !== $shop->id) {
            return false;
        }

        $member = $shop->shopMembers()->where('user_id', $user->id)->first();
        if (!$member || !$member->is_active) return false;

        return in_array('*', $member->permissions) ||
               in_array('transfer_stock', $member->permissions);
    }

    /**
     * Determine whether the user can delete the purchase order.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder, Shop $shop): bool
    {
        if ($purchaseOrder->buyer_shop_id !== $shop->id) {
            return false;
        }

        $member = $shop->shopMembers()->where('user_id', $user->id)->first();
        if (!$member || !$member->is_active) return false;

        return in_array('*', $member->permissions); // Only shop owner can delete purchase orders
    }
}
