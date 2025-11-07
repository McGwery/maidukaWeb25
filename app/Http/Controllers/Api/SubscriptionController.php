<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelSubscriptionRequest;
use App\Http\Requests\RenewSubscriptionRequest;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions for the specified shop.
     */
    public function index(Request $request, Shop $shop): JsonResponse
    {
        $subscriptions = $shop->subscriptions()
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->plan, function ($query, $plan) {
                $query->where('plan', $plan);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->isActive, function ($query) {
                $query->active();
            })
            ->when($request->isExpired, function ($query) {
                $query->expired();
            })
            ->when($request->isExpiringSoon, function ($query) {
                $query->expiringSoon();
            })
            ->when($request->sortBy && $request->sortDirection, function ($query) use ($request) {
                $query->orderBy($request->sortBy, $request->sortDirection);
            }, function ($query) {
                $query->orderBy('starts_at', 'desc');
            })
            ->paginate($request->perPage ?? 15);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'subscriptions' => SubscriptionResource::collection($subscriptions),
                'pagination' => [
                    'total' => $subscriptions->total(),
                    'currentPage' => $subscriptions->currentPage(),
                    'lastPage' => $subscriptions->lastPage(),
                    'perPage' => $subscriptions->perPage(),
                ]
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Get the current active subscription for the shop.
     */
    public function current(Shop $shop): JsonResponse
    {
        $subscription = $shop->activeSubscription;

        if (!$subscription) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'No active subscription found for this shop.',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => new SubscriptionResource($subscription)
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created subscription.
     */
    public function store(StoreSubscriptionRequest $request, Shop $shop): JsonResponse
    {
        $data = $request->validated();

        // Check if shop already has an active subscription
        $activeSubscription = $shop->activeSubscription;
        if ($activeSubscription) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_CONFLICT,
                'message' => 'Shop already has an active subscription. Please cancel or let it expire before creating a new one.',
                'data' => new SubscriptionResource($activeSubscription)
            ], Response::HTTP_CONFLICT);
        }

        try {
            DB::beginTransaction();

            $plan = SubscriptionPlan::from($data['plan']);

            $subscription = Subscription::create([
                'shop_id' => $shop->id,
                'plan' => $data['plan'],
                'type' => $data['type'],
                'status' => SubscriptionStatus::ACTIVE,
                'price' => $plan->price(),
                'currency' => $shop->currency,
                'starts_at' => now(),
                'expires_at' => now()->addDays($plan->durationDays()),
                'auto_renew' => $data['autoRenew'] ?? false,
                'payment_method' => $data['paymentMethod'] ?? null,
                'transaction_reference' => $data['transactionReference'] ?? null,
                'features' => $plan->features(),
                'max_users' => $this->getMaxUsers($plan),
                'max_products' => $this->getMaxProducts($plan),
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_CREATED,
                'message' => 'Subscription created successfully.',
                'data' => new SubscriptionResource($subscription)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create subscription.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified subscription.
     */
    public function show(Shop $shop, Subscription $subscription): JsonResponse
    {
        // Ensure subscription belongs to the shop
        if ($subscription->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'This subscription does not belong to the specified shop.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => new SubscriptionResource($subscription)
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified subscription.
     */
    public function update(UpdateSubscriptionRequest $request, Shop $shop, Subscription $subscription): JsonResponse
    {
        // Ensure subscription belongs to the shop
        if ($subscription->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'This subscription does not belong to the specified shop.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $updateData = [];

            if (isset($data['plan'])) {
                $plan = SubscriptionPlan::from($data['plan']);
                $updateData['plan'] = $data['plan'];
                $updateData['price'] = $plan->price();
                $updateData['features'] = $plan->features();
                $updateData['max_users'] = $this->getMaxUsers($plan);
                $updateData['max_products'] = $this->getMaxProducts($plan);
            }

            if (isset($data['type'])) {
                $updateData['type'] = $data['type'];
            }

            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }

            if (isset($data['autoRenew'])) {
                $updateData['auto_renew'] = $data['autoRenew'];
            }

            if (isset($data['paymentMethod'])) {
                $updateData['payment_method'] = $data['paymentMethod'];
            }

            if (isset($data['transactionReference'])) {
                $updateData['transaction_reference'] = $data['transactionReference'];
            }

            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }

            $subscription->update($updateData);

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_OK,
                'message' => 'Subscription updated successfully.',
                'data' => new SubscriptionResource($subscription->fresh())
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();

            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update subscription.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(CancelSubscriptionRequest $request, Shop $shop, Subscription $subscription): JsonResponse
    {
        // Ensure subscription belongs to the shop
        if ($subscription->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'This subscription does not belong to the specified shop.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        if ($subscription->status === SubscriptionStatus::CANCELLED) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_CONFLICT,
                'message' => 'This subscription is already cancelled.',
                'data' => new SubscriptionResource($subscription)
            ], Response::HTTP_CONFLICT);
        }

        $data = $request->validated();

        try {
            $subscription->cancel($data['reason'] ?? null);

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_OK,
                'message' => 'Subscription cancelled successfully.',
                'data' => new SubscriptionResource($subscription->fresh())
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to cancel subscription.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Renew the subscription.
     */
    public function renew(RenewSubscriptionRequest $request, Shop $shop, Subscription $subscription): JsonResponse
    {
        // Ensure subscription belongs to the shop
        if ($subscription->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'This subscription does not belong to the specified shop.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $durationDays = $data['durationDays'] ?? null;
            $subscription->renew($durationDays);

            if (isset($data['paymentMethod'])) {
                $subscription->update(['payment_method' => $data['paymentMethod']]);
            }

            if (isset($data['transactionReference'])) {
                $subscription->update(['transaction_reference' => $data['transactionReference']]);
            }

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_OK,
                'message' => 'Subscription renewed successfully.',
                'data' => new SubscriptionResource($subscription->fresh())
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();

            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to renew subscription.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Suspend the subscription.
     */
    public function suspend(Shop $shop, Subscription $subscription): JsonResponse
    {
        // Ensure subscription belongs to the shop
        if ($subscription->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'This subscription does not belong to the specified shop.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        if ($subscription->status === SubscriptionStatus::SUSPENDED) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_CONFLICT,
                'message' => 'This subscription is already suspended.',
                'data' => new SubscriptionResource($subscription)
            ], Response::HTTP_CONFLICT);
        }

        try {
            $subscription->suspend();

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_OK,
                'message' => 'Subscription suspended successfully.',
                'data' => new SubscriptionResource($subscription->fresh())
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to suspend subscription.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Activate the subscription.
     */
    public function activate(Shop $shop, Subscription $subscription): JsonResponse
    {
        // Ensure subscription belongs to the shop
        if ($subscription->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'This subscription does not belong to the specified shop.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        if ($subscription->status === SubscriptionStatus::ACTIVE) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_CONFLICT,
                'message' => 'This subscription is already active.',
                'data' => new SubscriptionResource($subscription)
            ], Response::HTTP_CONFLICT);
        }

        try {
            $subscription->activate();

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_OK,
                'message' => 'Subscription activated successfully.',
                'data' => new SubscriptionResource($subscription->fresh())
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to activate subscription.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get subscription plans with their details.
     */
    public function plans(): JsonResponse
    {
        $plans = collect(SubscriptionPlan::cases())->map(function ($plan) {
            return [
                'value' => $plan->value,
                'label' => $plan->label(),
                'price' => $plan->price(),
                'durationDays' => $plan->durationDays(),
                'features' => $plan->features(),
            ];
        });

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'plans' => $plans
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Get subscription statistics for the shop.
     */
    public function statistics(Shop $shop): JsonResponse
    {
        $stats = [
            'totalSubscriptions' => $shop->subscriptions()->count(),
            'activeSubscriptions' => $shop->subscriptions()->active()->count(),
            'expiredSubscriptions' => $shop->subscriptions()->expired()->count(),
            'cancelledSubscriptions' => $shop->subscriptions()->where('status', SubscriptionStatus::CANCELLED)->count(),
            'expiringSoonSubscriptions' => $shop->subscriptions()->expiringSoon()->count(),
            'currentSubscription' => $shop->activeSubscription ? new SubscriptionResource($shop->activeSubscription) : null,
            'totalSpent' => $shop->subscriptions()
                ->where('status', '!=', SubscriptionStatus::CANCELLED)
                ->sum('price'),
        ];

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => $stats
        ], Response::HTTP_OK);
    }

    /**
     * Get max users based on plan.
     */
    private function getMaxUsers(SubscriptionPlan $plan): ?int
    {
        return match ($plan) {
            SubscriptionPlan::FREE => 1,
            SubscriptionPlan::BASIC => 3,
            SubscriptionPlan::PREMIUM => 10,
            SubscriptionPlan::ENTERPRISE => null, // unlimited
        };
    }

    /**
     * Get max products based on plan.
     */
    private function getMaxProducts(SubscriptionPlan $plan): ?int
    {
        return match ($plan) {
            SubscriptionPlan::FREE => 50,
            SubscriptionPlan::BASIC => 500,
            SubscriptionPlan::PREMIUM => null, // unlimited
            SubscriptionPlan::ENTERPRISE => null, // unlimited
        };
    }
}

