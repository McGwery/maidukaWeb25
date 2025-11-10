<?php

namespace App\Http\Controllers\Api;

use App\Enums\ShopMemberRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShopMemberRequest;
use App\Http\Resources\ShopMemberResource;
use App\Models\Shop;
use App\Models\ShopMember;
use App\Traits\HasStandardResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ShopMemberController extends Controller
{
    use AuthorizesRequests, HasStandardResponse;

    public function index(Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('view', $shop);

        $members = $shop->shopMembers()
            ->with('user')
            ->latest()
            ->paginate();

        $transformedMembers = $members->setCollection(collect(ShopMemberResource::collection($members->getCollection())));

        return $this->paginatedResponse(
            'Shop members retrieved successfully.',
            $transformedMembers
        );
    }

    public function store(ShopMemberRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('create', $shop);

        $role = ShopMemberRole::from($request->input('role'));

        // Check if user is already a member
        if ($shop->members()->wherePivot('user_id', $request->input('user_id'))->exists()) {
            return $this->errorResponse(
                'User is already a member of this shop.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Create the shop member association using attach
        $shop->members()->attach($request->input('user_id'), [
            'id' => Str::uuid()->toString(),
            'role' => $role->value,
            'permissions' => $request->input('permissions', $role->permissions()),
        ]);

        $member = $shop->shopMembers()->where('user_id', $request->input('user_id'))->first();

        return $this->successResponse(
            'Shop member added successfully.',
            ['member' => new ShopMemberResource($member->load('user'))],
            Response::HTTP_CREATED
        );
    }

    public function show(Shop $shop, ShopMember $member): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('view', $member);

        if ($member->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Shop member not found.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->successResponse(
            'Shop member retrieved successfully.',
            ['member' => new ShopMemberResource($member->load('user'))]
        );
    }

    public function update(ShopMemberRequest $request, Shop $shop, ShopMember $member): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('update', $member);

        if ($member->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Shop member not found.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        // Prevent changing owner's role
        if ($member->role === ShopMemberRole::OWNER->value) {
            return $this->errorResponse(
                'Cannot modify owner\'s role.',
                null,
                Response::HTTP_FORBIDDEN
            );
        }

        $role = ShopMemberRole::from($request->input('role'));

        $member->update([
            'role' => $role->value,
            'permissions' => $request->input('permissions', $role->permissions()),
        ]);

        return $this->successResponse(
            'Shop member updated successfully.',
            ['member' => new ShopMemberResource($member->load('user'))]
        );
    }

    public function destroy(Shop $shop, ShopMember $member): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('delete', $member);

        if ($member->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Shop member not found.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        // Prevent removing the owner
        if ($member->role === ShopMemberRole::OWNER->value) {
            return $this->errorResponse(
                'Cannot remove shop owner.',
                null,
                Response::HTTP_FORBIDDEN
            );
        }

        $member->delete();

        return $this->successResponse('Shop member removed successfully.');
    }
}
