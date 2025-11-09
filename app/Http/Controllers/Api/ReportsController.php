<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\ShopMember;
use App\Policies\ReportPolicy;
use App\Traits\HasDateRangeFilter;
use App\Traits\HasStandardResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ReportsController extends Controller
{
    use HasDateRangeFilter, HasStandardResponse;

    /**
     * Get sales report
     */
    public function salesReport(Request $request): JsonResponse
    {
        $this->initRequestTime();

        $this->validateDateFilter($request);
        $dateRange = $this->getDateRange($request);
        $shopId = $request->user()->activeShop->shop_id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        // Authorization
        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('viewSalesReport', [ReportPolicy::class, $shop]);

        // Total sales metrics
        $salesMetrics = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->selectRaw('
                COUNT(*) as totalSales,
                SUM(total_amount) as totalRevenue,
                SUM(amount_paid) as totalPaid,
                SUM(debt_amount) as totalDebt,
                SUM(profit_amount) as totalProfit,
                AVG(total_amount) as averageSaleValue
            ')
            ->first();

        // Sales by status
        $salesByStatus = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('status')
            ->get()
            ->map(fn($item) => [
                'status' => $item->status->value,
                'statusLabel' => $item->status->label(),
                'count' => (int) $item->count,
                'total' => (float) $item->total,
            ]);

        // Sales by payment status
        $salesByPaymentStatus = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->select('payment_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_status')
            ->get()
            ->map(fn($item) => [
                'paymentStatus' => $item->payment_status,
                'count' => (int) $item->count,
                'total' => (float) $item->total,
            ]);

        // Top customers by purchase amount
        $topCustomers = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as purchaseCount'), DB::raw('SUM(total_amount) as totalSpent'))
            ->groupBy('customer_id')
            ->orderByDesc('totalSpent')
            ->limit(10)
            ->with('customer:id,name,phone')
            ->get()
            ->map(fn($item) => [
                'customerId' => $item->customer_id,
                'customerName' => $item->customer?->name,
                'customerPhone' => $item->customer?->phone,
                'purchaseCount' => (int) $item->purchaseCount,
                'totalSpent' => (float) $item->totalSpent,
            ]);

        // Daily sales breakdown
        $dailySales = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->selectRaw('DATE(sale_date) as date, COUNT(*) as count, SUM(total_amount) as total, SUM(profit_amount) as profit')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'salesCount' => (int) $item->count,
                'totalRevenue' => (float) $item->total,
                'totalProfit' => (float) $item->profit,
            ]);

        return $this->successResponse(
            'Sales report generated successfully.',
            [
                'period' => [
                    'filter' => $request->input('dateFilter', 'today'),
                    'startDate' => $dateRange['startDate']->toDateString(),
                    'endDate' => $dateRange['endDate']->toDateString(),
                ],
                'summary' => [
                    'totalSales' => (int) $salesMetrics->totalSales,
                    'totalRevenue' => (float) $salesMetrics->totalRevenue,
                    'totalPaid' => (float) $salesMetrics->totalPaid,
                    'totalDebt' => (float) $salesMetrics->totalDebt,
                    'totalProfit' => (float) $salesMetrics->totalProfit,
                    'averageSaleValue' => (float) $salesMetrics->averageSaleValue,
                ],
                'salesByStatus' => $salesByStatus,
                'salesByPaymentStatus' => $salesByPaymentStatus,
                'topCustomers' => $topCustomers,
                'dailyBreakdown' => $dailySales,
            ]
        );
    }

    /**
     * Get products report
     */
    public function productsReport(Request $request): JsonResponse
    {
        $this->initRequestTime();

        $this->validateDateFilter($request);
        $dateRange = $this->getDateRange($request);
        $shopId = $request->user()->activeShop->shop_id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        // Authorization
        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('viewProductsReport', [ReportPolicy::class, $shop]);

        // Total products
        $totalProducts = Product::where('shop_id', $shopId)->count();
        $lowStockProducts = Product::where('shop_id', $shopId)
            ->whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->count();
        $outOfStockProducts = Product::where('shop_id', $shopId)
            ->where('current_stock', 0)
            ->count();

        // Inventory value
        $inventoryValue = Product::where('shop_id', $shopId)
            ->selectRaw('SUM(current_stock * cost_per_unit) as totalCost, SUM(current_stock * price_per_unit) as potentialRevenue')
            ->first();

        // Top selling products (by quantity)
        $topSellingByQuantity = SaleItem::whereHas('sale', function ($query) use ($shopId, $dateRange) {
            $query->where('shop_id', $shopId)
                ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']]);
        })
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as totalQuantity'), DB::raw('SUM(total) as totalRevenue'), DB::raw('SUM(profit) as totalProfit'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('totalQuantity')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'productId' => $item->product_id,
                'productName' => $item->product_name,
                'quantitySold' => (float) $item->totalQuantity,
                'totalRevenue' => (float) $item->totalRevenue,
                'totalProfit' => (float) $item->totalProfit,
            ]);

        // Top selling products (by revenue)
        $topSellingByRevenue = SaleItem::whereHas('sale', function ($query) use ($shopId, $dateRange) {
            $query->where('shop_id', $shopId)
                ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']]);
        })
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as totalQuantity'), DB::raw('SUM(total) as totalRevenue'), DB::raw('SUM(profit) as totalProfit'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('totalRevenue')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'productId' => $item->product_id,
                'productName' => $item->product_name,
                'quantitySold' => (float) $item->totalQuantity,
                'totalRevenue' => (float) $item->totalRevenue,
                'totalProfit' => (float) $item->totalProfit,
            ]);

        // Low stock alert
        $lowStockAlert = Product::where('shop_id', $shopId)
            ->whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->select('id', 'product_name', 'sku', 'current_stock', 'low_stock_threshold')
            ->orderBy('current_stock')
            ->limit(20)
            ->get()
            ->map(fn($item) => [
                'productId' => $item->id,
                'productName' => $item->product_name,
                'sku' => $item->sku,
                'currentStock' => (int) $item->current_stock,
                'lowStockThreshold' => (int) $item->low_stock_threshold,
            ]);

        // Product categories breakdown
        $categoryBreakdown = Product::where('shop_id', $shopId)
            ->whereNotNull('category_id')
            ->with('category:id,name')
            ->select('category_id', DB::raw('COUNT(*) as productCount'), DB::raw('SUM(current_stock) as totalStock'))
            ->groupBy('category_id')
            ->get()
            ->map(fn($item) => [
                'categoryId' => $item->category_id,
                'categoryName' => $item->category?->name,
                'productCount' => (int) $item->productCount,
                'totalStock' => (int) $item->totalStock,
            ]);

        return $this->successResponse(
            'Products report generated successfully.',
            [
                'period' => [
                    'filter' => $request->input('dateFilter', 'today'),
                    'startDate' => $dateRange['startDate']->toDateString(),
                    'endDate' => $dateRange['endDate']->toDateString(),
                ],
                'summary' => [
                    'totalProducts' => $totalProducts,
                    'lowStockProducts' => $lowStockProducts,
                    'outOfStockProducts' => $outOfStockProducts,
                    'inventoryCostValue' => (float) ($inventoryValue->totalCost ?? 0),
                    'potentialRevenue' => (float) ($inventoryValue->potentialRevenue ?? 0),
                    'expectedProfit' => (float) (($inventoryValue->potentialRevenue ?? 0) - ($inventoryValue->totalCost ?? 0)),
                ],
                'topSellingByQuantity' => $topSellingByQuantity,
                'topSellingByRevenue' => $topSellingByRevenue,
                'lowStockAlert' => $lowStockAlert,
                'categoryBreakdown' => $categoryBreakdown,
            ]
        );
    }

    /**
     * Get financial report
     */
    public function financialReport(Request $request): JsonResponse
    {
        $this->initRequestTime();

        $this->validateDateFilter($request);
        $dateRange = $this->getDateRange($request);
        $shopId = $request->user()->activeShop->shop_id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        // Authorization
        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('viewFinancialReport', [ReportPolicy::class, $shop]);

        // Revenue from sales
        $salesMetrics = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->selectRaw('
                SUM(total_amount) as totalRevenue,
                SUM(amount_paid) as totalPaid,
                SUM(debt_amount) as totalDebt,
                SUM(profit_amount) as grossProfit
            ')
            ->first();

        // Total expenses
        $expensesMetrics = Expense::where('shop_id', $shopId)
            ->whereBetween('created_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->selectRaw('SUM(amount) as totalExpenses, COUNT(*) as expenseCount')
            ->first();

        // Expenses by category
        $expensesByCategory = Expense::where('shop_id', $shopId)
            ->whereBetween('created_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category->value,
                'categoryLabel' => $item->category->label(),
                'total' => (float) $item->total,
                'count' => (int) $item->count,
            ]);

        // Calculate net profit
        $grossProfit = (float) ($salesMetrics->grossProfit ?? 0);
        $totalExpenses = (float) ($expensesMetrics->totalExpenses ?? 0);
        $netProfit = $grossProfit - $totalExpenses;

        // Get savings settings and calculate savings
        $savingsSettings = \App\Models\ShopSavingsSetting::where('shop_id', $shopId)->first();

        $savingsData = [
            'isEnabled' => false,
            'currentBalance' => 0.00,
            'totalSavedInPeriod' => 0.00,
            'proposedSavingsAmount' => 0.00,
            'savingsType' => null,
            'savingsPercentage' => null,
            'fixedAmount' => null,
            'netProfitAfterSavings' => $netProfit,
        ];

        if ($savingsSettings && $savingsSettings->is_enabled) {
            // Calculate proposed savings based on net profit
            $proposedSavings = 0;
            if ($netProfit > 0) {
                $proposedSavings = $savingsSettings->calculateSavingsAmount($netProfit);
            }

            // Get actual savings in period
            $actualSavings = \App\Models\SavingsTransaction::where('shop_id', $shopId)
                ->where('type', 'deposit')
                ->whereBetween('transaction_date', [$dateRange['startDate'], $dateRange['endDate']])
                ->sum('amount');

            $savingsData = [
                'isEnabled' => true,
                'currentBalance' => (float) $savingsSettings->current_balance,
                'totalSavedInPeriod' => (float) $actualSavings,
                'proposedSavingsAmount' => (float) $proposedSavings,
                'savingsType' => $savingsSettings->savings_type,
                'savingsPercentage' => $savingsSettings->savings_type === 'percentage' ? (float) $savingsSettings->savings_percentage : null,
                'fixedAmount' => $savingsSettings->savings_type === 'fixed_amount' ? (float) $savingsSettings->fixed_amount : null,
                'netProfitAfterSavings' => $netProfit - $proposedSavings,
                'targetAmount' => (float) $savingsSettings->target_amount,
                'progressPercentage' => $savingsSettings->getProgressPercentage(),
            ];
        }

        // Outstanding debts
        $outstandingDebts = Customer::where('shop_id', $shopId)
            ->where('current_debt', '>', 0)
            ->selectRaw('COUNT(*) as customerCount, SUM(current_debt) as totalDebt')
            ->first();

        // Cash flow overview
        $cashIn = (float) ($salesMetrics->totalPaid ?? 0);
        $cashOut = $totalExpenses;
        $cashFlow = $cashIn - $cashOut;

        // Daily financial breakdown with savings
        $dailyFinancials = DB::table('sales')
            ->where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->selectRaw('DATE(sale_date) as date')
            ->selectRaw('SUM(total_amount) as revenue')
            ->selectRaw('SUM(amount_paid) as cashIn')
            ->selectRaw('SUM(profit_amount) as grossProfit')
            ->groupBy('date')
            ->get()
            ->map(function ($sale) use ($shopId, $dateRange, $savingsSettings) {
                $expenseForDate = Expense::where('shop_id', $shopId)
                    ->whereDate('created_at', $sale->date)
                    ->sum('amount');

                $dailyNetProfit = (float) ($sale->grossProfit - $expenseForDate);

                // Calculate proposed savings for this day
                $proposedDailySavings = 0;
                if ($savingsSettings && $savingsSettings->is_enabled && $dailyNetProfit > 0) {
                    $proposedDailySavings = $savingsSettings->calculateSavingsAmount($dailyNetProfit);
                }

                // Get actual savings for this day
                $actualDailySavings = \App\Models\SavingsTransaction::where('shop_id', $shopId)
                    ->where('type', 'deposit')
                    ->whereDate('transaction_date', $sale->date)
                    ->sum('amount');

                return [
                    'date' => $sale->date,
                    'revenue' => (float) $sale->revenue,
                    'cashIn' => (float) $sale->cashIn,
                    'expenses' => (float) $expenseForDate,
                    'grossProfit' => (float) $sale->grossProfit,
                    'netProfit' => $dailyNetProfit,
                    'proposedSavings' => (float) $proposedDailySavings,
                    'actualSavings' => (float) $actualDailySavings,
                    'netProfitAfterSavings' => $dailyNetProfit - $proposedDailySavings,
                ];
            });

        // Profit margin
        $totalRevenue = (float) ($salesMetrics->totalRevenue ?? 0);
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return $this->successResponse(
            'Financial report generated successfully.',
            [
                'period' => [
                    'filter' => $request->input('dateFilter', 'today'),
                    'startDate' => $dateRange['startDate']->toDateString(),
                    'endDate' => $dateRange['endDate']->toDateString(),
                ],
                'summary' => [
                    'totalRevenue' => $totalRevenue,
                    'cashReceived' => (float) ($salesMetrics->totalPaid ?? 0),
                    'grossProfit' => $grossProfit,
                    'totalExpenses' => $totalExpenses,
                    'netProfit' => $netProfit,
                    'profitMargin' => round($profitMargin, 2),
                    'cashFlow' => $cashFlow,
                ],
                'savings' => $savingsData,
                'expenses' => [
                    'total' => $totalExpenses,
                    'count' => (int) ($expensesMetrics->expenseCount ?? 0),
                    'byCategory' => $expensesByCategory,
                ],
                'receivables' => [
                    'totalDebtInPeriod' => (float) ($salesMetrics->totalDebt ?? 0),
                    'outstandingDebts' => (float) ($outstandingDebts->totalDebt ?? 0),
                    'customersWithDebt' => (int) ($outstandingDebts->customerCount ?? 0),
                ],
                'cashFlow' => [
                    'cashIn' => $cashIn,
                    'cashOut' => $cashOut,
                    'netCashFlow' => $cashFlow,
                ],
                'dailyBreakdown' => $dailyFinancials,
            ]
        );
    }

    /**
     * Get employees report
     */
    public function employeesReport(Request $request): JsonResponse
    {
        $this->initRequestTime();

        $this->validateDateFilter($request);
        $dateRange = $this->getDateRange($request);
        $shopId = $request->user()->activeShop->shop_id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        // Authorization
        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('viewEmployeesReport', [ReportPolicy::class, $shop]);

        // Total team members
        $totalMembers = ShopMember::where('shop_id', $shopId)->count();
        $activeMembers = ShopMember::where('shop_id', $shopId)
            ->where('is_active', true)
            ->count();

        // Sales by employee
        $employeeSales = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->whereNotNull('user_id')
            ->select('user_id',
                DB::raw('COUNT(*) as salesCount'),
                DB::raw('SUM(total_amount) as totalRevenue'),
                DB::raw('SUM(profit_amount) as totalProfit')
            )
            ->groupBy('user_id')
            ->with('user:id,name,email')
            ->get()
            ->map(fn($item) => [
                'userId' => $item->user_id,
                'userName' => $item->user?->name,
                'userEmail' => $item->user?->email,
                'salesCount' => (int) $item->salesCount,
                'totalRevenue' => (float) $item->totalRevenue,
                'totalProfit' => (float) $item->totalProfit,
                'averageSaleValue' => $item->salesCount > 0 ? (float) ($item->totalRevenue / $item->salesCount) : 0,
            ])
            ->sortByDesc('totalRevenue')
            ->values();

        // Top performers
        $topPerformers = $employeeSales->take(5);

        // Team members list with roles
        $teamMembers = ShopMember::where('shop_id', $shopId)
            ->with('user:id,name,email,phone')
            ->get()
            ->map(fn($member) => [
                'userId' => $member->user_id,
                'userName' => $member->user?->name,
                'userEmail' => $member->user?->email,
                'userPhone' => $member->user?->phone,
                'role' => $member->role,
                'isActive' => (bool) $member->is_active,
                'joinedAt' => $member->created_at?->toDateString(),
            ]);

        // Daily employee performance
        $dailyPerformance = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->whereNotNull('user_id')
            ->selectRaw('DATE(sale_date) as date, user_id, COUNT(*) as salesCount, SUM(total_amount) as revenue')
            ->groupBy('date', 'user_id')
            ->with('user:id,name')
            ->get()
            ->groupBy('date')
            ->map(function ($salesByDate, $date) {
                return [
                    'date' => $date,
                    'employees' => $salesByDate->map(fn($sale) => [
                        'userId' => $sale->user_id,
                        'userName' => $sale->user?->name,
                        'salesCount' => (int) $sale->salesCount,
                        'revenue' => (float) $sale->revenue,
                    ])->values(),
                ];
            })
            ->values();

        return $this->successResponse(
            'Employees report generated successfully.',
            [
                'period' => [
                    'filter' => $request->input('dateFilter', 'today'),
                    'startDate' => $dateRange['startDate']->toDateString(),
                    'endDate' => $dateRange['endDate']->toDateString(),
                ],
                'summary' => [
                    'totalMembers' => $totalMembers,
                    'activeMembers' => $activeMembers,
                    'inactiveMembers' => $totalMembers - $activeMembers,
                ],
                'employeePerformance' => $employeeSales,
                'topPerformers' => $topPerformers,
                'teamMembers' => $teamMembers,
                'dailyPerformance' => $dailyPerformance,
            ]
        );
    }

    /**
     * Get overview/dashboard report (all key metrics)
     */
    public function overviewReport(Request $request): JsonResponse
    {
        $this->initRequestTime();

        $this->validateDateFilter($request);
        $dateRange = $this->getDateRange($request);
        $shopId = $request->user()->activeShop->shop_id ?? null;

        if (!$shopId) {
            return $this->errorResponse(
                'No active shop selected.',
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        // Authorization
        $shop = Shop::findOrFail($shopId);
//        Gate::authorize('viewOverviewReport', [ReportPolicy::class, $shop]);

        // Sales summary
        $salesSummary = Sale::where('shop_id', $shopId)
            ->whereBetween('sale_date', [$dateRange['startDate'], $dateRange['endDate']])
            ->selectRaw('
                COUNT(*) as totalSales,
                SUM(total_amount) as totalRevenue,
                SUM(profit_amount) as totalProfit
            ')
            ->first();

        // Products summary
        $productsSummary = [
            'totalProducts' => Product::where('shop_id', $shopId)->count(),
            'lowStockProducts' => Product::where('shop_id', $shopId)
                ->whereColumn('current_stock', '<=', 'low_stock_threshold')
                ->count(),
        ];

        // Financial summary
        $expensesTotal = Expense::where('shop_id', $shopId)
            ->whereBetween('created_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->sum('amount');


        $financialSummary = [
            'totalRevenue' => (float) ($salesSummary->totalRevenue ?? 0),
            'grossProfit' => (float) ($salesSummary->totalProfit ?? 0),
            'totalExpenses' => (float) $expensesTotal,
            'netProfit' => (float) (($salesSummary->totalProfit ?? 0) - $expensesTotal),
        ];

        // Employees summary
        $employeesSummary = [
            'totalMembers' => ShopMember::where('shop_id', $shopId)->count(),
            'activeMembers' => ShopMember::where('shop_id', $shopId)->where('is_active', true)->count(),
        ];

        // Customers summary
        $customersSummary = [
            'totalCustomers' => Customer::where('shop_id', $shopId)->count(),
            'customersWithDebt' => Customer::where('shop_id', $shopId)->where('current_debt', '>', 0)->count(),
        ];

        return $this->successResponse(
            'Overview report generated successfully.',
            [
                'period' => [
                    'filter' => $request->input('dateFilter', 'today'),
                    'startDate' => $dateRange['startDate']->toDateString(),
                    'endDate' => $dateRange['endDate']->toDateString(),
                ],
                'sales' => [
                    'totalSales' => (int) ($salesSummary->totalSales ?? 0),
                    'totalRevenue' => (float) ($salesSummary->totalRevenue ?? 0),
                    'totalProfit' => (float) ($salesSummary->totalProfit ?? 0),
                ],
                'products' => $productsSummary,
                'financial' => $financialSummary,
                'employees' => $employeesSummary,
                'customers' => $customersSummary,
            ]
        );
    }
}
