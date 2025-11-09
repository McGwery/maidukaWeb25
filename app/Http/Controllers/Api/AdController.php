<?php

namespace App\Http\Controllers\Api;

use App\Enums\AdStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\TrackAdClickRequest;
use App\Http\Requests\TrackAdViewRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Http\Resources\AdResource;
use App\Models\Ad;
use App\Models\AdClick;
use App\Models\AdConversion;
use App\Models\AdPerformanceDaily;
use App\Models\AdView;
use App\Models\Shop;
use App\Traits\HasStandardResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AdController extends Controller
{
    use HasStandardResponse;
    /**
     * Get ads feed for mobile app (Deals tab).
     * Personalized based on user's shop.
     */
    public function feed(Request $request): JsonResponse
    {
        $this->initRequestTime();

        $user = $request->user();
        $shopId = $request->query('shopId');

        // Get user's current shop
        $shop = $shopId ? Shop::find($shopId) : $user->shops()->first();

        if (!$shop) {
            return $this->errorResponse(
                'No shop found for user.',
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        // Get personalized ads
        $ads = Ad::active()
            ->forShop($shop)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->perPage ?? 20);

        $transformedAds = $ads->setCollection(collect(AdResource::collection($ads->getCollection())));

        return $this->paginatedResponse(
            'Ads retrieved successfully.',
            $transformedAds,
            [
                'shopInfo' => [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'businessType' => $shop->business_type->value,
                ],
            ]
        );
    }

    /**
     * Get ads for a shop
     */
    public function index(Request $request): JsonResponse
    {
        $this->initRequestTime();

        $user = $request->user();
        $shopId = $request->query('shopId');

        $query = Ad::with(['shop', 'creator', 'approver']);

        // Filter by shop if shop owner
        if ($shopId) {
            $shop = Shop::findOrFail($shopId);

            // Authorization
            $this->authorize('viewAny', [Ad::class, $shop]);

            $query->where('shop_id', $shop->id);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by placement
        if ($request->placement) {
            $query->byPlacement($request->placement);
        }

        // Filter by active/inactive
        if ($request->has('isActive')) {
            $query->where('is_active', $request->boolean('isActive'));
        }

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sortBy = $request->sortBy ?? 'created_at';
        $sortDirection = $request->sortDirection ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $ads = $query->paginate($request->perPage ?? 15);

        $transformedAds = $ads->setCollection(collect(AdResource::collection($ads->getCollection())));

        return $this->paginatedResponse(
            'Ads retrieved successfully.',
            $transformedAds
        );
    }

    /**
     * Create a new ad (shop owner or admin).
     */
    public function store(StoreAdRequest $request): JsonResponse
    {
        $this->initRequestTime();

        $user = $request->user();
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Determine shop_id
            $shopId = $request->shopId ?? null;

            // If shop is creating ad, ensure they have active subscription
            if ($shopId) {
                $shop = Shop::findOrFail($shopId);

                // Authorization
                $this->authorize('create', [Ad::class, $shop]);

                // Check if shop has active subscription
                $activeSubscription = $shop->activeSubscription;
                if (!$activeSubscription) {
                    DB::rollBack();
                    return $this->errorResponse(
                        'Active subscription required to create ads.',
                        null,
                        Response::HTTP_FORBIDDEN
                    );
                }

                // Check monthly ad limit (at least 1 ad per month)
                $currentMonthAds = Ad::where('shop_id', $shop->id)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count();

                // Premium plans can have more ads
                $adLimit = $this->getAdLimitForPlan($activeSubscription->plan->value);

                if ($currentMonthAds >= $adLimit) {
                    DB::rollBack();
                    return $this->errorResponse(
                        "Monthly ad limit reached. Your plan allows {$adLimit} ad(s) per month.",
                        [
                            'currentAds' => $currentMonthAds,
                            'limit' => $adLimit,
                        ],
                        Response::HTTP_FORBIDDEN
                    );
                }
            }

            // Convert camelCase to snake_case
            $adData = [
                'shop_id' => $shopId,
                'title' => $data['title'],
                'description' => $data['description'],
                'image_url' => $data['imageUrl'] ?? null,
                'video_url' => $data['videoUrl'] ?? null,
                'media_type' => $data['mediaType'],
                'cta_text' => $data['ctaText'] ?? 'Learn More',
                'cta_url' => $data['ctaUrl'] ?? null,
                'target_categories' => $data['targetCategories'] ?? null,
                'target_shop_types' => $data['targetShopTypes'] ?? null,
                'target_location' => $data['targetLocation'] ?? null,
                'target_all' => $data['targetAll'] ?? false,
                'ad_type' => $data['adType'],
                'placement' => $data['placement'],
                'priority' => $data['priority'] ?? 0,
                'starts_at' => $data['startsAt'],
                'expires_at' => $data['expiresAt'],
                'budget' => $data['budget'] ?? null,
                'cost_per_click' => $data['costPerClick'] ?? 0,
                'status' => $shopId ? AdStatus::PENDING : AdStatus::APPROVED, // Admin ads auto-approved
                'created_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ];

            $ad = Ad::create($adData);

            DB::commit();

            return $this->successResponse(
                'Ad created successfully.',
                new AdResource($ad->load(['shop', 'creator'])),
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to create ad.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get specific ad details.
     */
    public function show(Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('view', $ad);

        $ad->load(['shop', 'creator', 'approver']);

        return $this->successResponse(
            'Ad retrieved successfully.',
            new AdResource($ad)
        );
    }

    /**
     * Update ad.
     */
    public function update(UpdateAdRequest $request, Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('update', $ad);

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $updateData = [];

            foreach ($data as $key => $value) {
                $snakeKey = $this->camelToSnake($key);
                $updateData[$snakeKey] = $value;
            }

            $ad->update($updateData);

            DB::commit();

            return $this->successResponse(
                'Ad updated successfully.',
                new AdResource($ad->fresh(['shop', 'creator', 'approver']))
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to update ad.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete ad.
     */
    public function destroy(Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('delete', $ad);

        try {
            $ad->delete();

            return $this->successResponse('Ad deleted successfully.');

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete ad.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Track ad view.
     */
    public function trackView(TrackAdViewRequest $request, Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        $user = $request->user();
        $data = $request->validated();

        try {
            // Check if user already viewed this ad recently (within last hour)
            $recentView = AdView::where('ad_id', $ad->id)
                ->where('user_id', $user->id)
                ->where('viewed_at', '>', now()->subHour())
                ->first();

            $isUnique = !$recentView;

            // Create view record
            AdView::create([
                'ad_id' => $ad->id,
                'user_id' => $user->id,
                'shop_id' => $data['shopId'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $data['deviceType'] ?? null,
                'platform' => $data['platform'] ?? null,
                'viewed_at' => now(),
                'view_duration' => $data['viewDuration'] ?? null,
            ]);

            // Increment counters
            $ad->incrementViews($isUnique);

            // Update daily performance
            $this->updateDailyPerformance($ad, 'view', $isUnique);

            return $this->successResponse('View tracked successfully.');

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to track view.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Track ad click.
     */
    public function trackClick(TrackAdClickRequest $request, Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        $user = $request->user();
        $data = $request->validated();

        try {
            // Check if user already clicked this ad recently (within last hour)
            $recentClick = AdClick::where('ad_id', $ad->id)
                ->where('user_id', $user->id)
                ->where('clicked_at', '>', now()->subHour())
                ->first();

            $isUnique = !$recentClick;

            // Create click record
            AdClick::create([
                'ad_id' => $ad->id,
                'user_id' => $user->id,
                'shop_id' => $data['shopId'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $data['deviceType'] ?? null,
                'platform' => $data['platform'] ?? null,
                'click_location' => $data['clickLocation'] ?? null,
                'clicked_at' => now(),
            ]);

            // Increment counters
            $ad->incrementClicks($isUnique);

            // Update daily performance
            $this->updateDailyPerformance($ad, 'click', $isUnique);

            return $this->successResponse(
                'Click tracked successfully.',
                ['ctaUrl' => $ad->cta_url]
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to track click.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get ad analytics.
     */
    public function analytics(Request $request, Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('viewAnalytics', $ad);

        $period = $request->period ?? 'week'; // week, month, all

        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'all' => $ad->created_at,
            default => now()->subWeek(),
        };

        // Get daily performance
        $dailyStats = AdPerformanceDaily::where('ad_id', $ad->id)
            ->where('date', '>=', $startDate->toDateString())
            ->orderBy('date')
            ->get();

        // Get conversion data
        $conversions = AdConversion::where('ad_id', $ad->id)
            ->where('converted_at', '>=', $startDate)
            ->selectRaw('conversion_type, COUNT(*) as count, SUM(conversion_value) as total_value')
            ->groupBy('conversion_type')
            ->get();

        // Get demographics
        $deviceStats = AdView::where('ad_id', $ad->id)
            ->where('viewed_at', '>=', $startDate)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        $platformStats = AdView::where('ad_id', $ad->id)
            ->where('viewed_at', '>=', $startDate)
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->get();

        return $this->successResponse(
            'Ad analytics retrieved successfully.',
            [
                'overview' => [
                    'totalViews' => $ad->view_count,
                    'uniqueViews' => $ad->unique_view_count,
                    'totalClicks' => $ad->click_count,
                    'uniqueClicks' => $ad->unique_click_count,
                    'ctr' => $ad->ctr,
                    'totalSpent' => $ad->total_spent,
                    'remainingBudget' => $ad->budget ? $ad->budget - $ad->total_spent : null,
                ],
                'dailyPerformance' => $dailyStats->map(fn($stat) => [
                    'date' => $stat->date->format('Y-m-d'),
                    'views' => $stat->views,
                    'uniqueViews' => $stat->unique_views,
                    'clicks' => $stat->clicks,
                    'uniqueClicks' => $stat->unique_clicks,
                    'conversions' => $stat->conversions,
                    'ctr' => $stat->ctr,
                    'cost' => $stat->cost,
                ]),
                'conversions' => $conversions->map(fn($conv) => [
                    'type' => $conv->conversion_type,
                    'count' => $conv->count,
                    'totalValue' => $conv->total_value,
                ]),
                'demographics' => [
                    'devices' => $deviceStats->map(fn($stat) => [
                        'type' => $stat->device_type,
                        'count' => $stat->count,
                    ]),
                    'platforms' => $platformStats->map(fn($stat) => [
                        'platform' => $stat->platform,
                        'count' => $stat->count,
                    ]),
                ],
            ]
        );
    }

    /**
     * Approve ad (admin only).
     */
    public function approve(Request $request, Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        $user = $request->user();

        try {
            $ad->update([
                'status' => AdStatus::APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            return $this->successResponse(
                'Ad approved successfully.',
                new AdResource($ad->fresh(['shop', 'creator', 'approver']))
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to approve ad.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Reject ad (admin only).
     */
    public function reject(Request $request, Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = $request->user();

        try {
            $ad->update([
                'status' => AdStatus::REJECTED,
                'rejection_reason' => $request->reason,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            return $this->successResponse(
                'Ad rejected.',
                new AdResource($ad->fresh(['shop', 'creator', 'approver']))
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to reject ad.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Pause/unpause ad.
     */
    public function togglePause(Ad $ad): JsonResponse
    {
        $this->initRequestTime();

        try {
            $newStatus = $ad->status === AdStatus::PAUSED ? AdStatus::APPROVED : AdStatus::PAUSED;

            $ad->update(['status' => $newStatus]);

            return $this->successResponse(
                $newStatus === AdStatus::PAUSED ? 'Ad paused.' : 'Ad resumed.',
                new AdResource($ad->fresh())
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to toggle ad status.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get ad limit based on subscription plan.
     */
    private function getAdLimitForPlan(string $plan): int
    {
        return match($plan) {
            'free' => 1,
            'basic' => 2,
            'premium' => 5,
            'enterprise' => 10,
            default => 1,
        };
    }

    /**
     * Update daily performance stats.
     */
    private function updateDailyPerformance(Ad $ad, string $type, bool $isUnique): void
    {
        $today = now()->toDateString();

        $performance = AdPerformanceDaily::firstOrCreate(
            ['ad_id' => $ad->id, 'date' => $today],
            ['views' => 0, 'unique_views' => 0, 'clicks' => 0, 'unique_clicks' => 0, 'conversions' => 0]
        );

        if ($type === 'view') {
            $performance->increment('views');
            if ($isUnique) {
                $performance->increment('unique_views');
            }
        } elseif ($type === 'click') {
            $performance->increment('clicks');
            if ($isUnique) {
                $performance->increment('unique_clicks');
            }

            // Update cost
            if ($ad->cost_per_click > 0) {
                $performance->increment('cost', $ad->cost_per_click);
            }
        }

        // Update CTR
        if ($performance->views > 0) {
            $ctr = ($performance->clicks / $performance->views) * 100;
            $performance->update(['ctr' => round($ctr, 2)]);
        }
    }

    /**
     * Convert camelCase to snake_case.
     */
    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}

