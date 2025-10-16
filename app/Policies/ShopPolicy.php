<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Shop $shop): bool
    {
        return $user->id === $shop->owner_id ||
            $shop->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Shop $shop): bool
    {
        return $user->id === $shop->owner_id;
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $user->id === $shop->owner_id;
    }
}