<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSavingsGoalRequest;
use App\Http\Requests\SavingsDepositRequest;
use App\Http\Requests\SavingsWithdrawalRequest;
use App\Http\Requests\UpdateSavingsGoalRequest;
use App\Http\Requests\UpdateSavingsSettingsRequest;
use App\Http\Resources\SavingsGoalResource;
use App\Http\Resources\SavingsTransactionResource;
use App\Http\Resources\ShopSavingsSettingResource;
use App\Models\Shop;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use App\Models\ShopSavingsSetting;
use App\Policies\SavingsPolicy;
use App\Traits\HasStandardResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class SavingsController extends Controller
{
    use HasStandardResponse;

    /**
     * Get or create savings settings for shop
     */
    public function getSettings(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_OK
            );
        }

        // Authorization
//        Gate::authorize('view', [SavingsPolicy::class, $shop]);

        $settings = ShopSavingsSetting::firstOrCreate(
            ['shop_id' => $shopId],
            [
                'is_enabled' => false,
                'savings_type' => 'percentage',
                'savings_percentage' => 10.00,
                'withdrawal_frequency' => 'monthly',
            ]
        );

        return $this->successResponse(
            'Savings settings retrieved successfully.',
            new ShopSavingsSettingResource($settings)
        );
    }

    /**
     * Update savings settings
     */
    public function updateSettings(UpdateSavingsSettingsRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_OK
            );
        }

        // Authorization
//        Gate::authorize('manageSettings', [SavingsPolicy::class, $shop]);

        $validated = $request->validated();

        $settings = ShopSavingsSetting::firstOrCreate(['shop_id' => $shopId]);

        // Map request keys to model columns
        $updateData = [];

        if (isset($validated['proposed_amount'])) {
            $updateData['fixed_amount'] = $validated['proposed_amount'];
        }

        if (isset($validated['proposed_percentage'])) {
            $updateData['savings_percentage'] = $validated['proposed_percentage'];
        }

        if (isset($validated['saving_goal'])) {
            $updateData['target_amount'] = $validated['saving_goal'];
        }

        if (isset($validated['start_date'])) {
            $updateData['last_savings_date'] = $validated['start_date'];
        }

        if (isset($validated['end_date'])) {
            $updateData['target_date'] = $validated['end_date'];
        }

        if (isset($validated['frequency'])) {
            $updateData['withdrawal_frequency'] = $validated['frequency'];
        }

        if (isset($validated['enabled'])) {
            $updateData['is_enabled'] = $validated['enabled'];
        }

        // notes field doesn't exist in model, so we skip it or you can add it to the migration

        $settings->update($updateData);

        return $this->successResponse(
            'Savings settings updated successfully.',
            [
                'id' => $settings->id,
                'proposedAmount' => (float) $settings->fixed_amount,
                'proposedPercentage' => (float) $settings->savings_percentage,
                'savingGoal' => (float) $settings->target_amount,
                'startDate' => $settings->last_savings_date?->format('Y-m-d'),
                'endDate' => $settings->target_date?->format('Y-m-d'),
                'frequency' => $settings->withdrawal_frequency,
                'enabled' => $settings->is_enabled,
                'currentBalance' => (float) $settings->current_balance,
            ]
        );
    }

    /**
     * Make a manual savings deposit
     */
    public function deposit(SavingsDepositRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_OK
            );
        }

        // Authorization
//        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('deposit', [SavingsPolicy::class, $shop]);

        $validated = $request->validated();

        $settings = ShopSavingsSetting::firstOrCreate(['shop_id' => $shopId]);

        DB::transaction(function () use ($settings, $validated, $request) {
            $balanceBefore = $settings->current_balance;
            $amount = $validated['amount'];
            $balanceAfter = $balanceBefore + $amount;

            // Create transaction
            $transaction = SavingsTransaction::create([
                'shop_id' => $settings->shop_id,
                'savings_goal_id' => $validated['savingsGoalId'] ?? null,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'transaction_date' => now(),
                'is_automatic' => false,
                'description' => $validated['description'] ?? 'Manual deposit',
                'processed_by' => $request->user()->id,
            ]);

            // Update settings
            $settings->update([
                'current_balance' => $balanceAfter,
                'total_saved' => $settings->total_saved + $amount,
                'last_savings_date' => now(),
            ]);

            // Update goal if specified
            if ($validated['savingsGoalId'] ?? null) {
                $goal = SavingsGoal::find($validated['savingsGoalId']);
                if ($goal) {
                    $goal->increment('current_amount', $amount);
                }
            }
        });

        $settings->refresh();

        return $this->successResponse(
            'Deposit successful.',
            [
                'currentBalance' => (float) $settings->current_balance,
                'totalSaved' => (float) $settings->total_saved,
            ]
        );
    }

    /**
     * Withdraw from savings
     */
    public function withdraw(SavingsWithdrawalRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_OK
            );
        }

        $validated = $request->validated();

        $settings = ShopSavingsSetting::where('shop_id', $shopId)->firstOrFail();

        // Check if withdrawal is possible
        if ($settings->current_balance < $validated['amount']) {
            return $this->errorResponse(
                'Insufficient savings balance.',
                null,
                Response::HTTP_OK
            );
        }

        DB::transaction(function () use ($settings, $validated, $request) {
            $balanceBefore = $settings->current_balance;
            $amount = $validated['amount'];
            $balanceAfter = $balanceBefore - $amount;

            // Create transaction
            SavingsTransaction::create([
                'shop_id' => $settings->shop_id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'transaction_date' => now(),
                'is_automatic' => false,
                'description' => $validated['description'] ?? 'Manual withdrawal',
                'notes' => $validated['notes'] ?? null,
                'processed_by' => $request->user()->id,
            ]);

            // Update settings
            $settings->update([
                'current_balance' => $balanceAfter,
                'total_withdrawn' => $settings->total_withdrawn + $amount,
                'last_withdrawal_date' => now(),
            ]);
        });

        $settings->refresh();

        return $this->successResponse(
            'Withdrawal successful.',
            [
                'currentBalance' => (float) $settings->current_balance,
                'totalWithdrawn' => (float) $settings->total_withdrawn,
            ]
        );
    }

    /**
     * Get savings transactions history
     */
    public function getTransactions(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_OK
            );
        }

        // Authorization
//        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('view', [SavingsPolicy::class, $shop]);

        $type = $request->query('type'); // 'deposit', 'withdrawal', or null for all
        $limit = $request->query('limit', 50);

        $query = SavingsTransaction::where('shop_id', $shopId)
            ->with(['processedBy:id,name', 'savingsGoal:id,name']);

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $this->successResponse(
            'Savings transactions retrieved successfully.',
            SavingsTransactionResource::collection($transactions)
        );
    }

    /**
     * Get savings summary and analytics
     */
    public function getSummary(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_OK
            );
        }

        $settings = ShopSavingsSetting::firstOrCreate(['shop_id' => $shopId]);

        // Get transaction statistics
        $totalDeposits = SavingsTransaction::where('shop_id', $shopId)
            ->where('type', 'deposit')
            ->sum('amount');

        $totalWithdrawals = SavingsTransaction::where('shop_id', $shopId)
            ->where('type', 'withdrawal')
            ->sum('amount');

        $automaticSavings = SavingsTransaction::where('shop_id', $shopId)
            ->where('type', 'deposit')
            ->where('is_automatic', true)
            ->sum('amount');

        $manualSavings = SavingsTransaction::where('shop_id', $shopId)
            ->where('type', 'deposit')
            ->where('is_automatic', false)
            ->sum('amount');

        // Monthly breakdown (last 6 months)
        $monthlyData = SavingsTransaction::where('shop_id', $shopId)
            ->where('transaction_date', '>=', now()->subMonths(6))
            ->selectRaw('
                DATE_FORMAT(transaction_date, "%Y-%m") as month,
                type,
                SUM(amount) as total
            ')
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(function ($transactions, $month) {
                $deposits = $transactions->where('type', 'deposit')->sum('total');
                $withdrawals = $transactions->where('type', 'withdrawal')->sum('total');

                return [
                    'month' => $month,
                    'deposits' => (float) $deposits,
                    'withdrawals' => (float) $withdrawals,
                    'netSavings' => (float) ($deposits - $withdrawals),
                ];
            })
            ->values();

        return $this->successResponse(
            'Savings summary retrieved successfully.',
            [
                'currentBalance' => (float) $settings->current_balance,
                'totalSaved' => (float) $totalDeposits,
                'totalWithdrawn' => (float) $totalWithdrawals,
                'automaticSavings' => (float) $automaticSavings,
                'manualSavings' => (float) $manualSavings,
                'targetAmount' => (float) $settings->target_amount,
                'progressPercentage' => $settings->getProgressPercentage(),
                'isEnabled' => $settings->is_enabled,
                'savingsType' => $settings->savings_type,
                'withdrawalFrequency' => $settings->withdrawal_frequency,
                'monthlyBreakdown' => $monthlyData,
            ]
        );
    }

    /**
     * Create a new savings goal
     */
    public function createGoal(CreateSavingsGoalRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_OK
            );
        }

        // Authorization
//        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('manageSettings', [SavingsPolicy::class, $shop]);

        $validated = $request->validated();

        $goal = SavingsGoal::create([
            'shop_id' => $shopId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_amount' => $validated['targetAmount'],
            'target_date' => $validated['targetDate'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'priority' => $validated['priority'] ?? 0,
            'status' => 'active',
        ]);

        return $this->successResponse(
            'Savings goal created successfully.',
            new SavingsGoalResource($goal),
            Response::HTTP_CREATED
        );
    }

    /**
     * Get all savings goals for the shop
     */
    public function getGoals(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_OK
            );
        }

        // Authorization
//        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('view', [SavingsPolicy::class, $shop]);

        $status = $request->query('status'); // 'active', 'completed', 'cancelled', or null for all

        $query = SavingsGoal::where('shop_id', $shopId);

        if ($status) {
            $query->where('status', $status);
        }

        $goals = $query->orderBy('priority', 'desc')
            ->orderBy('target_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            'Savings goals retrieved successfully.',
            SavingsGoalResource::collection($goals)
        );
    }

    /**
     * Update a savings goal
     */
    public function updateGoal(UpdateSavingsGoalRequest $request, Shop $shop, SavingsGoal $goal): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId || $goal->shop_id !== $shopId) {
            return $this->errorResponse(
                'Unauthorized.',
                null,
                Response::HTTP_OK
            );
        }

        $validated = $request->validated();

        $goal->update(array_filter([
            'name' => $validated['name'] ?? $goal->name,
            'description' => $validated['description'] ?? $goal->description,
            'target_amount' => $validated['targetAmount'] ?? $goal->target_amount,
            'target_date' => $validated['targetDate'] ?? $goal->target_date,
            'status' => $validated['status'] ?? $goal->status,
            'icon' => $validated['icon'] ?? $goal->icon,
            'color' => $validated['color'] ?? $goal->color,
            'priority' => $validated['priority'] ?? $goal->priority,
        ]));

        return $this->successResponse(
            'Savings goal updated successfully.',
            new SavingsGoalResource($goal)
        );
    }

    /**
     * Delete a savings goal
     */
    public function deleteGoal(Request $request, Shop $shop, SavingsGoal $goal): JsonResponse
    {
        $this->initRequestTime();

        $shopId = $shop->id ?? null;

        if (!$shopId || $goal->shop_id !== $shopId) {
            return $this->errorResponse(
                'Unauthorized.',
                null,
                Response::HTTP_OK
            );
        }

        $goal->delete();

        return $this->successResponse('Savings goal deleted successfully.');
    }
}

