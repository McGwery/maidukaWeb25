<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CreateShopRequest;
use App\Http\Requests\Shop\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Category;
use App\Models\Shop;
use App\Models\ShopSettings;
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
            ->with('owner')
            ->get()
            ->union($user->memberShops()->with('owner')->get())
            ->values();

        return $this->successResponse(
            'Shops retrieved successfully.',
            [
                'shops' => ShopResource::collection($shops),
                'activeShop' => $user->activeShop ? new ShopResource($user->activeShop->shop) : null,
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

            // Make this the active shop for the user if they don't have one
            if (!auth()->user()->activeShop) {
                auth()->user()->switchShop($shop);
            }

            DB::commit();

            return $this->successResponse(
                'Shop created successfully.',
                ['shop' => new ShopResource($shop->load('owner'))],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to create shop.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
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
                Response::HTTP_FORBIDDEN
            );
        }

        return $this->successResponse(
            'Shop retrieved successfully.',
            ['shop' => new ShopResource($shop->load(['owner', 'members']))]
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
                Response::HTTP_FORBIDDEN
            );
        }

        $shop->update($request->validated());

        return $this->successResponse(
            'Shop updated successfully.',
            ['shop' => new ShopResource($shop->load('owner'))]
        );
    }

      public function destroy(Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('delete', $shop);

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
                ['shop' => new ShopResource($shop->load('owner'))]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                null,
                Response::HTTP_FORBIDDEN
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
                Response::HTTP_FORBIDDEN
            );
        }

        $shop->is_active = !$shop->is_active;
        $shop->save();

        return $this->successResponse(
            'Shop status updated successfully.',
            ['shop' => new ShopResource($shop->load('owner'))]
        );
    }

}
