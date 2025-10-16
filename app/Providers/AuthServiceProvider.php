<?php

namespace App\Providers;

use App\Models\Shop;
use App\Policies\ShopPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Shop::class => ShopPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}