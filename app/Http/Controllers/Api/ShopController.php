<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CreateShopRequest;
use App\Http\Requests\Shop\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Models\ShopSettings;
use App\Models\Subscription;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Enums\Currency;
use App\Traits\HasStandardResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends Controller
{
    use HasStandardResponse;

    /**
     * Get all shops for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $this->initRequestTime();

        $user = auth()->user();

        $shops = $user->ownedShops()
            ->with(['owner', 'activeSubscription'])
            ->get()
            ->union($user->memberShops()->with(['owner', 'activeSubscription'])->get())
            ->values();
        return $this->successResponse(
            'Shops retrieved successfully.',
            [
                'shops' => ShopResource::collection($shops),
                'activeShop' => $user->activeShop ? new ShopResource($user->activeShop?->shop?->load('activeSubscription')) : null,
                'totalShops' => $shops->count(),
                'activeShops' => $shops->where('status', 'active')->count(),
            ]
        );
    }

    /**
     * Create a new shop.
     */
    public function store(CreateShopRequest $request): JsonResponse
    {
        $this->initRequestTime();

        try {
            DB::beginTransaction();

            $shop = Shop::create([
                ...$request->validated(),
                'owner_id' => auth()->id(),
            ]);

            // Create shop settings with defaults
            ShopSettings::create(array_merge(
                ['shop_id' => $shop->id],
                ShopSettings::defaults()
            ));

            // Create PREMIUM subscription for the new shop
            $premiumPlan = SubscriptionPlan::PREMIUM;
            $subscription = Subscription::create([
                'shop_id' => $shop->id,
                'plan' => $premiumPlan,
                'type' => SubscriptionType::BOTH,
                'status' => SubscriptionStatus::ACTIVE,
                'price' => $premiumPlan->price(),
                'currency' => $shop->currency ?? Currency::TZS,
                'starts_at' => now(),
                'expires_at' => now()->addDays($premiumPlan->durationDays()),
                'auto_renew' => false,
                'payment_method' => 'free_trial',
                'transaction_reference' => 'SHOP_CREATION_' . strtoupper(uniqid()),
                'features' => $premiumPlan->features(),
                'max_users' => 10,
                'max_products' => null, // Unlimited
                'notes' => 'Premium subscription activated on shop creation',
            ]);

            // Make this the active shop for the user if they don't have one
            if (!auth()->user()->activeShop) {
                auth()->user()->switchShop($shop);
            }

            if($request->image_url){
                $shop->clearMediaCollection('shop_images');
                $shop->addMediaFromBase64($request->image_url)
                    ->usingFileName('shop_image_' . $shop->id . '.png')
                    ->toMediaCollection('shop_images');
                $shop->update(['image_url' => $shop->getFirstMediaUrl('shop_images')]);
            }

            DB::commit();

            return $this->successResponse(
                'Shop created successfully with Premium subscription.',
                ['shop' => new ShopResource($shop->load(['owner', 'activeSubscription']))],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to create shop.',
                ['error' => $e->getMessage()],
                Response::HTTP_OK
            );
        }
    }

    /**
     * Get shop details.
     */
    public function show(Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        if (!auth()->user()->canAccessShop($shop)) {
            return $this->errorResponse(
                'You do not have access to this shop.',
                null,
                Response::HTTP_OK
            );
        }

        return $this->successResponse(
            'Shop retrieved successfully.',
            ['shop' => new ShopResource($shop->load(['owner', 'members', 'activeSubscription']))]
        );
    }

    /**
     * Update shop details.
     */
    public function update(UpdateShopRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        if (!auth()->user()->hasShopPermission($shop, 'manage_shop')) {
            return $this->errorResponse(
                'You do not have permission to update this shop.',
                null,
                Response::HTTP_OK
            );
        }

        $shop->update($request->validated());

        return $this->successResponse(
            'Shop updated successfully.',
            ['shop' => new ShopResource($shop->load(['owner', 'activeSubscription']))]
        );
    }

      public function destroy(Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('delete', $shop);

        if($shop->activeShop()->exists()){
            return $this->errorResponse(
                'Cannot delete the shop while it is set as active for a user.',
                null,
                Response::HTTP_OK
            );
        }

        $shop->delete();

        return $this->successResponse('Shop removed successfully.');
    }

    /**
     * Switch active shop.
     */
    public function switchShop(Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        try {
            auth()->user()->switchShop($shop);

            return $this->successResponse(
                'Successfully switched to ' . $shop->name . '.',
                ['shop' => new ShopResource($shop->load(['owner', 'activeSubscription']))]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                null,
                Response::HTTP_OK
            );
        }
    }

    /**
     * Set shop active status.
     */
    public function setActive(Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        if (!auth()->user()->hasShopPermission($shop, 'manage_shop')) {
            return $this->errorResponse(
                'You do not have permission to update this shop.',
                null,
                Response::HTTP_OK
            );
        }

        $shop->is_active = !$shop->is_active;
        $shop->save();

        return $this->successResponse(
            'Shop status updated successfully.',
            ['shop' => new ShopResource($shop->load(['owner', 'activeSubscription']))]
        );
    }

}
