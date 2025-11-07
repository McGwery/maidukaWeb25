<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExpenseCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request, Shop $shop): JsonResponse
    {
        // Authorization
        $this->authorize('viewAny', [Expense::class, $shop]);

        $expenses = $shop->expenses()
            ->with(['recordedBy'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('receipt_number', 'like', "%{$search}%");
                });
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->startDate, function ($query, $startDate) {
                $query->whereDate('expense_date', '>=', $startDate);
            })
            ->when($request->endDate, function ($query, $endDate) {
                $query->whereDate('expense_date', '<=', $endDate);
            })
            ->when($request->paymentMethod, function ($query, $method) {
                $query->where('payment_method', $method);
            })
            ->when($request->sortBy && $request->sortDirection, function ($query) use ($request) {
                $query->orderBy($request->sortBy, $request->sortDirection);
            }, function ($query) {
                $query->orderBy('expense_date', 'desc');
            })
            ->paginate($request->perPage ?? 15);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'expenses' => ExpenseResource::collection($expenses),
                'pagination' => [
                    'total' => $expenses->total(),
                    'currentPage' => $expenses->currentPage(),
                    'lastPage' => $expenses->lastPage(),
                    'perPage' => $expenses->perPage(),
                ]
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created expense.
     */
    public function store(StoreExpenseRequest $request, Shop $shop): JsonResponse
    {
        // Authorization
        $this->authorize('create', [Expense::class, $shop]);

        $data = $request->validated();
        $data['shop_id'] = $shop->id;
        $data['recorded_by'] = $request->user()->id;

        // Convert camelCase to snake_case for database
        $expense = Expense::create([
            'shop_id' => $data['shop_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'amount' => $data['amount'],
            'expense_date' => $data['expenseDate'],
            'payment_method' => $data['paymentMethod'],
            'receipt_number' => $data['receiptNumber'] ?? null,
            'attachment_url' => $data['attachmentUrl'] ?? null,
            'recorded_by' => $data['recorded_by'],
        ]);

        $expense->load(['recordedBy']);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_CREATED,
            'message' => 'Expense recorded successfully.',
            'data' => new ExpenseResource($expense)
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified expense.
     */
    public function show(Shop $shop, Expense $expense): JsonResponse
    {
        // Authorization
        $this->authorize('view', $expense);

        if ($expense->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Expense not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $expense->load(['recordedBy']);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => new ExpenseResource($expense)
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified expense.
     */
    public function update(UpdateExpenseRequest $request, Shop $shop, Expense $expense): JsonResponse
    {
        // Authorization
        $this->authorize('update', $expense);

        if ($expense->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Expense not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        // Convert camelCase to snake_case for database
        $updateData = [];
        if (isset($data['title'])) $updateData['title'] = $data['title'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['category'])) $updateData['category'] = $data['category'];
        if (isset($data['amount'])) $updateData['amount'] = $data['amount'];
        if (isset($data['expenseDate'])) $updateData['expense_date'] = $data['expenseDate'];
        if (isset($data['paymentMethod'])) $updateData['payment_method'] = $data['paymentMethod'];
        if (isset($data['receiptNumber'])) $updateData['receipt_number'] = $data['receiptNumber'];
        if (isset($data['attachmentUrl'])) $updateData['attachment_url'] = $data['attachmentUrl'];

        $expense->update($updateData);
        $expense->load(['recordedBy']);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Expense updated successfully.',
            'data' => new ExpenseResource($expense)
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Shop $shop, Expense $expense): JsonResponse
    {
        // Authorization
        $this->authorize('delete', $expense);

        if ($expense->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Expense not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $expense->delete();

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Expense deleted successfully.'
        ], Response::HTTP_OK);
    }

    /**
     * Get expense summary with analytics.
     */
    public function summary(Request $request, Shop $shop): JsonResponse
    {
        // Authorization
        $this->authorize('viewSummary', [Expense::class, $shop]);

        // Validate date filters
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        // Base query
        $query = Expense::where('shop_id', $shop->id);

        if ($startDate) {
            $query->whereDate('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('expense_date', '<=', $endDate);
        }

        // Get total expenses
        $totalExpenses = (float) $query->sum('amount');

        // Get category breakdown
        $categoryBreakdown = DB::table('expenses')
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->where('shop_id', $shop->id)
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('expense_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('expense_date', '<=', $endDate);
            })
            ->whereNull('deleted_at')
            ->groupBy('category')
            ->get()
            ->map(function ($item) use ($totalExpenses) {
                $categoryEnum = ExpenseCategory::from($item->category);
                return [
                    'category' => [
                        'value' => $categoryEnum->value,
                        'label' => $categoryEnum->label(),
                    ],
                    'totalAmount' => (float) $item->total,
                    'count' => (int) $item->count,
                    'percentage' => $totalExpenses > 0 ? round(($item->total / $totalExpenses) * 100, 2) : 0,
                ];
            });

        // Get payment method breakdown
        $paymentMethodBreakdown = DB::table('expenses')
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->where('shop_id', $shop->id)
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('expense_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('expense_date', '<=', $endDate);
            })
            ->whereNull('deleted_at')
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                $methodEnum = \App\Enums\PaymentMethod::from($item->payment_method);
                return [
                    'paymentMethod' => [
                        'value' => $methodEnum->value,
                        'label' => $methodEnum->label(),
                    ],
                    'totalAmount' => (float) $item->total,
                    'count' => (int) $item->count,
                ];
            });

        // Get monthly trend (last 12 months or within date range)
        $monthlyTrend = DB::table('expenses')
            ->select(
                DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->where('shop_id', $shop->id)
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('expense_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('expense_date', '<=', $endDate);
            })
            ->whereNull('deleted_at')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'totalAmount' => (float) $item->total,
                    'count' => (int) $item->count,
                ];
            });

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'totalExpenses' => $totalExpenses,
                'categoryBreakdown' => $categoryBreakdown,
                'paymentMethodBreakdown' => $paymentMethodBreakdown,
                'monthlyTrend' => $monthlyTrend,
                'dateRange' => [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ],
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Get expense categories for dropdown
     */
    public function categories(): JsonResponse
    {
        $categories = collect(ExpenseCategory::cases())->map(function ($category) {
            return [
                'value' => $category->value,
                'label' => $category->label(),
            ];
        });

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'categories' => $categories
            ]
        ], Response::HTTP_OK);
    }
}

