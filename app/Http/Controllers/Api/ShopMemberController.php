<?php

namespace App\Http\Controllers\Api;

use App\Enums\ShopMemberRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShopMemberRequest;
use App\Http\Resources\ShopMemberResource;
use App\Models\Shop;
use App\Models\ShopMember;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ShopMemberController extends Controller
{
    use AuthorizesRequests;

    public function index(Shop $shop): JsonResponse
    {
        $this->authorize('view', $shop);

        $members = $shop->members()
            ->with('user')
            ->latest()
            ->paginate();

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'members' => ShopMemberResource::collection($members),
                'pagination' => [
                    'total' => $members->total(),
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'per_page' => $members->perPage(),
                ]
            ]
        ]);
    }

    public function store(ShopMemberRequest $request, Shop $shop): JsonResponse
    {
        $role = ShopMemberRole::from($request->input('role'));
        
        // Check if user is already a member
        if ($shop->members()->where('user_id', $request->input('user_id'))->exists()) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'User is already a member of this shop'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $member = $shop->members()->create([
            'user_id' => $request->input('user_id'),
            'role' => $role->value,
            'permissions' => $request->input('permissions', $role->permissions()),
        ]);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_CREATED,
            'data' => [
                'member' => new ShopMemberResource($member->load('user'))
            ]
        ], Response::HTTP_CREATED);
    }

    public function show(Shop $shop, ShopMember $member): JsonResponse
    {
        $this->authorize('view', $shop);
        
        if ($member->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Shop member not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'member' => new ShopMemberResource($member->load('user'))
            ]
        ]);
    }

    public function update(ShopMemberRequest $request, Shop $shop, ShopMember $member): JsonResponse
    {
        if ($member->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Shop member not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Prevent changing owner's role
        if ($member->role === ShopMemberRole::OWNER->value) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'Cannot modify owner\'s role'
            ], Response::HTTP_FORBIDDEN);
        }

        $role = ShopMemberRole::from($request->input('role'));
        
        $member->update([
            'role' => $role->value,
            'permissions' => $request->input('permissions', $role->permissions()),
        ]);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'member' => new ShopMemberResource($member->load('user'))
            ]
        ]);
    }

    public function destroy(Shop $shop, ShopMember $member): JsonResponse
    {
        $this->authorize('manage-members', $shop);

        if ($member->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Shop member not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Prevent removing the owner
        if ($member->role === ShopMemberRole::OWNER->value) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'Cannot remove shop owner'
            ], Response::HTTP_FORBIDDEN);
        }

        $member->delete();

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => null
        ]);
    }
}