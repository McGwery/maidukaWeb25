<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\ActiveShop;
use App\Models\Shop;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends Controller
{
    use AuthorizesRequests;
    
    public function index(Request $request): AnonymousResourceCollection
    {
        $shops = Shop::query()
            ->active()
            ->forUser($request->user()->id)
            ->with('owner')
            ->latest()
            ->paginate();

        return ShopResource::collection($shops);
    }

    public function store(CreateShopRequest $request): ShopResource
    {
        $shop = Shop::create([
            'owner_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return new ShopResource($shop->load('owner'));
    }

    public function show(Shop $shop): ShopResource
    {
        $this->authorize('view', $shop);
        
        return new ShopResource($shop->load('owner'));
    }

    public function update(CreateShopRequest $request, Shop $shop): ShopResource
    {
        $this->authorize('update', $shop);

        $shop->update($request->validated());

        return new ShopResource($shop->load('owner'));
    }

    public function destroy(Shop $shop): JsonResponse
    {
        $this->authorize('delete', $shop);

        $shop->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function setActive(Shop $shop): JsonResponse
    {
        $this->authorize('view', $shop);

        ActiveShop::query()
            ->updateOrCreate(
                ['user_id' => auth()->id()],
                ['shop_id' => $shop->id]
            );

        return response()->json(['message' => 'Shop set as active']);
    }

    public function getActive(): ?ShopResource
    {
        $activeShop = ActiveShop::query()
            ->where('user_id', auth()->id())
            ->first();

        if (!$activeShop) {
            return null;
        }

        return new ShopResource($activeShop->shop->load('owner'));
    }
}