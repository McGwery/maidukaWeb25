<?php

namespace App\Providers;

use App\Models\Ad;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Message;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\ShopMember;
use App\Policies\AdPolicy;
use App\Policies\ChatPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\SalePolicy;
use App\Policies\ShopMemberPolicy;
use App\Policies\ShopPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Shop::class => ShopPolicy::class,
        Product::class => ProductPolicy::class,
        Sale::class => SalePolicy::class,
        Customer::class => CustomerPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        Expense::class => ExpensePolicy::class,
        ShopMember::class => ShopMemberPolicy::class,
        Ad::class => AdPolicy::class,
        Conversation::class => ChatPolicy::class,
        Message::class => ChatPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
