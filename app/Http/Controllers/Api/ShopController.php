<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CreateShopRequest;
use App\Http\Requests\Shop\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends Controller
{
    /**
     * Get all shops for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $shops = $user->ownedShops()
            ->with('owner')
            ->union($user->memberShops()->with('owner'))
            ->get();

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'shops' => ShopResource::collection($shops),
                'activeShop' => $user->activeShop ? new ShopResource($user->activeShop->shop) : null,
                'totalShops' => $shops->count(),
                'activeShops' => $shops->where('status', 'active')->count(),
            ]
        ]);
    }

    /**
     * Create a new shop.
     */
    public function store(CreateShopRequest $request): JsonResponse
    {
        $shop = Shop::create([
            ...$request->validated(),
            'owner_id' => auth()->id(),
        ]);

        // Make this the active shop for the user if they don't have one
        if (!auth()->user()->activeShop) {
            auth()->user()->switchShop($shop);
        }

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_CREATED,
            'message' => 'Shop created successfully',
            'data' => [
                'shop' => new ShopResource($shop->load('owner')),
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Get shop details.
     */
    public function show(Shop $shop): JsonResponse
    {
        if (!auth()->user()->canAccessShop($shop)) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'You do not have access to this shop'
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'shop' => new ShopResource($shop->load(['owner', 'members'])),
            ]
        ]);
    }

    /**
     * Update shop details.
     */
    public function update(UpdateShopRequest $request, Shop $shop): JsonResponse
    {
        if (!auth()->user()->hasShopPermission($shop, 'manage_shop')) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'You do not have permission to update this shop',
            ], Response::HTTP_FORBIDDEN);
        }

        $shop->update($request->validated());

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Shop updated successfully',
            'data' => [
                'shop' => new ShopResource($shop->load('owner')),
            ]
        ]);
    }

    /**
     * Switch active shop.
     */
    public function switchShop(Shop $shop): JsonResponse
    {
        try {
            $activeShop = auth()->user()->switchShop($shop);

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_OK,
                'message' => 'Successfully switched to ' . $shop->name,
                'data' => [
                    'shop' => new ShopResource($shop->load('owner')),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => $e->getMessage()
            ], Response::HTTP_FORBIDDEN);
        }
    }
}