<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\StockAdjustmentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteSaleRequest;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\SaleResource;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleRefund;
use App\Models\Shop;
use App\Models\StockAdjustment;
use App\Traits\HasStandardResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class POSController extends Controller
{
    use HasStandardResponse;
    /**
     * Complete a sale transaction
     */
    public function completeSale(CompleteSaleRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('create', [Sale::class, $shop]);

        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = $request->user();

            // Load shop settings
            $settings = $shop->settings;
            if (!$settings) {
                $settings = \App\Models\ShopSettings::create(array_merge(
                    ['shop_id' => $shop->id],
                    \App\Models\ShopSettings::defaults()
                ));
            }

            // Calculate totals
            $subtotal = collect($validated['items'])->sum('total');
            $taxRate = $validated['taxRate'] ?? 0;

            // Apply tax from settings if enabled
            if ($settings->show_tax_on_receipt && $taxRate === 0) {
                $taxRate = $settings->tax_percentage;
            }

            $taxAmount = ($subtotal * $taxRate) / 100;
            $discountAmount = $validated['discountAmount'] ?? 0;
            $discountPercentage = $validated['discountPercentage'] ?? 0;

            // Check if discounts are allowed
            if (($discountAmount > 0 || $discountPercentage > 0) && !$settings->allow_discounts) {
                DB::rollBack();
                return $this->errorResponse(
                    'Discounts are not allowed for this shop.',
                    null,
                    Response::HTTP_FORBIDDEN
                );
            }

            // Check maximum discount percentage
            if ($discountPercentage > 0 && $discountPercentage > $settings->max_discount_percentage) {
                DB::rollBack();
                return $this->errorResponse(
                    "Discount percentage cannot exceed {$settings->max_discount_percentage}%.",
                    [
                        'maxDiscountPercentage' => $settings->max_discount_percentage,
                        'requestedDiscount' => $discountPercentage
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            if ($discountPercentage > 0) {
                $discountAmount = ($subtotal * $discountPercentage) / 100;
            }

            $totalAmount = $subtotal + $taxAmount - $discountAmount;
            $amountReceived = $validated['amountReceived'];
            $change = $validated['change'] ?? 0;

            // Determine payment status and debt
            $paymentStatus = 'paid';
            $debtAmount = 0;

            if ($amountReceived < $totalAmount) {
                $debtAmount = $totalAmount - $amountReceived;
                $paymentStatus = $amountReceived > 0 ? 'partially_paid' : 'debt';

                // Check if credit sales are allowed
                if (!$settings->allow_credit_sales) {
                    DB::rollBack();
                    return $this->errorResponse(
                        'Credit sales are not allowed for this shop.',
                        null,
                        Response::HTTP_FORBIDDEN
                    );
                }
            }

            // Handle customer - create new or use existing
            $customerId = null;
            if (isset($validated['customer'])) {
                $customer = null;

                // If customer ID provided, fetch existing customer
                if (!empty($validated['customer']['id'])) {
                    $customer = Customer::find($validated['customer']['id']);
                }
                // If no ID but name provided, create new customer
                elseif (!empty($validated['customer']['name'])) {
                    // Check if customer is required for credit
                    if ($debtAmount > 0 && $settings->require_customer_for_credit && empty($validated['customer']['name'])) {
                        DB::rollBack();
                        return $this->errorResponse(
                            'Customer information is required for credit sales.',
                            null,
                            Response::HTTP_UNPROCESSABLE_ENTITY
                        );
                    }

                    $customer = Customer::create([
                        'shop_id' => $shop->id,
                        'name' => $validated['customer']['name'],
                        'phone' => $validated['customer']['phone'] ?? null,
                        'credit_limit' => 0, // Default no credit for new customers
                        'current_debt' => 0,
                    ]);
                }

                // If customer exists and there's debt, check credit limit
                if ($customer && $debtAmount > 0) {
                    if (!$customer->hasAvailableCredit($debtAmount)) {
                        DB::rollBack();
                        return $this->errorResponse(
                            'Customer does not have sufficient credit limit.',
                            [
                                'requiredCredit' => $debtAmount,
                                'availableCredit' => $customer->credit_limit - $customer->current_debt,
                            ],
                            Response::HTTP_UNPROCESSABLE_ENTITY
                        );
                    }
                }

                $customerId = $customer?->id;
            }

            // Calculate total profit
            $totalProfit = 0;

            // Create sale
            $sale = Sale::create([
                'shop_id' => $shop->id,
                'customer_id' => $customerId,
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountReceived,
                'change_amount' => $change,
                'debt_amount' => $debtAmount,
                'profit_amount' => 0, // Will update after items
                'status' => SaleStatus::COMPLETED,
                'payment_status' => $paymentStatus,
                'notes' => $validated['notes'] ?? null,
                'sale_date' => now(),
            ]);

            // Create sale items and update stock
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['id']);

                if (!$product) {
                    DB::rollBack();
                    return $this->errorResponse(
                        "Product {$itemData['name']} not found.",
                        null,
                        Response::HTTP_NOT_FOUND
                    );
                }

                // Check stock availability (skip for service products)
                $isPhysicalProduct = $product->product_type === \App\Enums\ProductType::PHYSICAL;

                if ($isPhysicalProduct && $settings->track_stock && $product->track_inventory) {
                    if (!$settings->allow_negative_stock && $product->current_stock < $itemData['quantity']) {
                        DB::rollBack();
                        return $this->errorResponse(
                            "Insufficient stock for {$product->product_name}. Available: {$product->current_stock}",
                            [
                                'productName' => $product->product_name,
                                'requestedQuantity' => $itemData['quantity'],
                                'availableStock' => $product->current_stock,
                            ],
                            Response::HTTP_UNPROCESSABLE_ENTITY
                        );
                    }

                    // Check low stock threshold and trigger notification if enabled
                    $newStock = $product->current_stock - $itemData['quantity'];
                    if ($settings->isStockLow($newStock)) {
                        // TODO: Queue low stock notification job
                        Log::info("Low stock alert for product: {$product->product_name}, Stock: {$newStock}");
                    }
                }

                $itemSubtotal = $itemData['currentPrice'] * $itemData['quantity'];
                $itemProfit = ($itemData['currentPrice'] - $product->cost_per_unit) * $itemData['quantity'];
                $totalProfit += $itemProfit;

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_sku' => $product->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_type' => $itemData['unit'] ?? $product->unit_type,
                    'original_price' => $itemData['originalPrice'],
                    'selling_price' => $itemData['currentPrice'],
                    'cost_price' => $product->cost_per_unit,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemData['total'],
                    'profit' => $itemProfit,
                ]);

                // Update product stock if auto-deduct is enabled (skip for service products)
                if ($isPhysicalProduct && $settings->auto_deduct_stock_on_sale && $settings->track_stock && $product->track_inventory) {
                    $oldStock = $product->current_stock;
                    $newStock = $oldStock - $itemData['quantity'];

                    $product->update(['current_stock' => $newStock]);

                    // Create stock adjustment record
                    StockAdjustment::create([
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                        'type' => StockAdjustmentType::ADJUSTMENT,
                        'quantity' => -$itemData['quantity'],
                        'value_at_time' => $product->cost_per_unit,
                        'previous_stock' => $oldStock,
                        'new_stock' => $newStock,
                        'reason' => "Sale #{$sale->sale_number}",
                        'notes' => "Sold via POS - Auto deducted",
                    ]);
                }
            }

            // Update sale profit
            $sale->update(['profit_amount' => $totalProfit]);

            // Create payment record
            SalePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $user->id,
                'payment_method' => $validated['paymentMethod'],
                'amount' => $amountReceived,
                'payment_date' => now(),
            ]);

            // Update customer debt if applicable
            if ($customerId && $debtAmount > 0) {
                $customer->addDebt($debtAmount);
            }

            DB::commit();

            // Load relationships
            $sale->load(['items', 'customer', 'payments', 'user']);

            return $this->successResponse(
                'Sale completed successfully.',
                ['sale' => new SaleResource($sale)],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to complete sale: ' . $e->getMessage(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get sales history with filters
     */
    public function getSales(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('viewAny', [Sale::class, $shop]);

        $query = Sale::where('shop_id', $shop->id)
            ->with(['customer', 'user', 'items'])
            ->withCount('items');

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->paymentStatus) {
            $query->where('payment_status', $request->paymentStatus);
        }

        if ($request->customerId) {
            $query->where('customer_id', $request->customerId);
        }

        if ($request->fromDate) {
            $query->whereDate('sale_date', '>=', $request->fromDate);
        }

        if ($request->toDate) {
            $query->whereDate('sale_date', '<=', $request->toDate);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', function ($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%");
                  });
            });
        }

        $sales = $query->latest('sale_date')->paginate($request->perPage ?? 15);

        $transformedSales = $sales->setCollection(collect(SaleResource::collection($sales->getCollection())));

        return $this->paginatedResponse(
            'Sales retrieved successfully.',
            $transformedSales
        );
    }

    /**
     * Get a specific sale
     */
    public function getSale(Shop $shop, Sale $sale): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('view', $sale);

        if ($sale->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Sale not found in this shop.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        $sale->load(['items.product', 'customer', 'payments.user', 'refunds', 'user']);

        return $this->successResponse(
            'Sale retrieved successfully.',
            ['sale' => new SaleResource($sale)]
        );
    }

    /**
     * Get sales analytics
     */
    public function getSalesAnalytics(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('viewAnalytics', [Sale::class, $shop]);

        $query = Sale::where('shop_id', $shop->id);
        $fromDate = $request->fromDate ?? now()->startOfMonth();
        $toDate = $request->toDate ?? now()->endOfMonth();

        $sales = Sale::where('shop_id', $shop->id)
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->where('status', SaleStatus::COMPLETED);

        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('total_amount');
        $totalProfit = $sales->sum('profit_amount');
        $totalDebt = $sales->sum('debt_amount');
        $averageSale = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Sales by payment method
        $salesByPaymentMethod = Sale::where('shop_id', $shop->id)
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->join('sale_payments', 'sales.id', '=', 'sale_payments.sale_id')
            ->select('sale_payments.payment_method', DB::raw('COUNT(DISTINCT sales.id) as count'), DB::raw('SUM(sale_payments.amount) as total'))
            ->groupBy('sale_payments.payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->payment_method => [
                    'count' => $item->count,
                    'total' => round($item->total, 2),
                ]];
            });

        // Top selling products
        $topProducts = SaleItem::whereHas('sale', function ($q) use ($shop, $fromDate, $toDate) {
                $q->where('shop_id', $shop->id)
                  ->whereBetween('sale_date', [$fromDate, $toDate])
                  ->where('status', SaleStatus::COMPLETED);
            })
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as totalQuantity'), DB::raw('SUM(total) as totalRevenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('totalRevenue')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'productId' => $item->product_id,
                    'productName' => $item->product_name,
                    'totalQuantity' => $item->totalQuantity,
                    'totalRevenue' => round($item->totalRevenue, 2),
                ];
            });

        // Sales by day
        $salesByDay = Sale::where('shop_id', $shop->id)
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->where('status', SaleStatus::COMPLETED)
            ->select(DB::raw('DATE(sale_date) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                    'revenue' => round($item->revenue, 2),
                ];
            });

        return $this->successResponse(
            'Sales analytics retrieved successfully.',
            [
                'summary' => [
                    'totalSales' => $totalSales,
                    'totalRevenue' => round($totalRevenue, 2),
                    'totalProfit' => round($totalProfit, 2),
                    'totalDebt' => round($totalDebt, 2),
                    'averageSale' => round($averageSale, 2),
                    'profitMargin' => $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0,
                ],
                'salesByPaymentMethod' => $salesByPaymentMethod,
                'topProducts' => $topProducts,
                'salesByDay' => $salesByDay,
                'period' => [
                    'from' => $fromDate,
                    'to' => $toDate,
                ]
            ]
        );
    }

    /**
     * Refund a sale
     */
    public function refundSale(Request $request, Shop $shop, Sale $sale): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('refund', $sale);

        if ($sale->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Sale not found in this shop.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        if (!$sale->canRefund()) {
            return $this->errorResponse(
                'This sale cannot be refunded.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->amount_paid,
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'restockItems' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Create refund record
            $refund = SaleRefund::create([
                'sale_id' => $sale->id,
                'user_id' => $request->user()->id,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'refund_date' => now(),
            ]);

            // Update sale status
            $totalRefunded = $sale->getTotalRefunded();

            if ($totalRefunded >= $sale->amount_paid) {
                $sale->update(['status' => SaleStatus::REFUNDED]);
            } else {
                $sale->update(['status' => SaleStatus::PARTIALLY_REFUNDED]);
            }

            // Restock items if requested (only for physical products)
            if ($request->restockItems) {
                foreach ($sale->items as $item) {
                    if ($item->product && $item->product->track_inventory && $item->product->product_type === \App\Enums\ProductType::PHYSICAL) {
                        $product = $item->product;
                        $oldStock = $product->current_stock;
                        $newStock = $oldStock + $item->quantity;

                        $product->update(['current_stock' => $newStock]);

                        // Create stock adjustment
                        StockAdjustment::create([
                            'product_id' => $product->id,
                            'user_id' => $request->user()->id,
                            'type' => StockAdjustmentType::ADJUSTMENT,
                            'quantity' => $item->quantity,
                            'value_at_time' => $product->cost_per_unit,
                            'previous_stock' => $oldStock,
                            'new_stock' => $newStock,
                            'reason' => "Refund for Sale #{$sale->sale_number}",
                            'notes' => $request->reason,
                        ]);
                    }
                }
            }

            DB::commit();

            $sale->load(['items', 'customer', 'payments', 'refunds']);

            return $this->successResponse(
                'Refund processed successfully.',
                [
                    'sale' => new SaleResource($sale),
                    'refund' => [
                        'id' => $refund->id,
                        'amount' => $refund->amount,
                        'reason' => $refund->reason,
                        'refundDate' => $refund->refund_date,
                    ]
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to process refund: ' . $e->getMessage(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Add payment to an existing sale
     */
    public function addPayment(Request $request, Shop $shop, Sale $sale): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('addPayment', $sale);

        if ($sale->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Sale not found in this shop.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        $remainingDebt = $sale->getRemainingDebt();

        if ($remainingDebt <= 0) {
            return $this->errorResponse(
                'This sale has no outstanding debt.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $request->validate([
            'paymentMethod' => ['required', 'string'],
            'amount' => 'required|numeric|min:0.01|max:' . $remainingDebt,
            'referenceNumber' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Create payment record
            SalePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $request->user()->id,
                'payment_method' => $request->paymentMethod,
                'amount' => $request->amount,
                'reference_number' => $request->referenceNumber,
                'notes' => $request->notes,
                'payment_date' => now(),
            ]);

            // Update sale payment status
            $newAmountPaid = $sale->amount_paid + $request->amount;
            $newDebtAmount = $sale->total_amount - $newAmountPaid;

            $paymentStatus = 'paid';
            if ($newDebtAmount > 0) {
                $paymentStatus = 'partially_paid';
            }

            $sale->update([
                'amount_paid' => $newAmountPaid,
                'debt_amount' => $newDebtAmount,
                'payment_status' => $paymentStatus,
            ]);

            // Update customer debt
            if ($sale->customer) {
                $sale->customer->reduceDebt($request->amount);
            }

            DB::commit();

            $sale->load(['payments', 'customer']);

            return $this->successResponse(
                'Payment added successfully.',
                [
                    'sale' => new SaleResource($sale),
                    'remainingDebt' => round($newDebtAmount, 2),
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to add payment: ' . $e->getMessage(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Customer Management

    /**
     * Get customers for a shop
     */
    public function getCustomers(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('viewAny', [Customer::class, $shop]);

        $query = Customer::where('shop_id', $shop->id);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->hasDebt) {
            $query->where('current_debt', '>', 0);
        }

        $customers = $query->latest()->paginate($request->perPage ?? 15);

        $transformedCustomers = $customers->setCollection(collect(CustomerResource::collection($customers->getCollection())));

        return $this->paginatedResponse(
            'Customers retrieved successfully.',
            $transformedCustomers
        );
    }

    /**
     * Create a new customer
     */
    public function createCustomer(CustomerRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('create', [Customer::class, $shop]);

        $customer = Customer::firstOrCreate([
            'shop_id' => $shop->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'credit_limit' => $request->creditLimit ?? 0,
            'notes' => $request->notes,
        ]);

        return $this->successResponse(
            'Customer created successfully.',
            ['customer' => new CustomerResource($customer)],
            Response::HTTP_CREATED
        );
    }

    /**
     * Update a customer
     */
    public function updateCustomer(CustomerRequest $request, Shop $shop, Customer $customer): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('update', $customer);

        if ($customer->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Customer not found in this shop.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'credit_limit' => $request->creditLimit ?? $customer->credit_limit,
            'notes' => $request->notes,
        ]);

        return $this->successResponse(
            'Customer updated successfully.',
            ['customer' => new CustomerResource($customer)]
        );
    }

    /**
     * Get a specific customer
     */
    public function getCustomer(Shop $shop, Customer $customer): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('view', $customer);

        if ($customer->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Customer not found in this shop.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->load(['sales' => function ($query) {
            $query->latest()->limit(10);
        }]);

        $totalSales = $customer->sales()->count();
        $totalDebt = $customer->current_debt;

        return $this->successResponse(
            'Customer retrieved successfully.',
            [
                'customer' => new CustomerResource($customer),
                'recentSales' => SaleResource::collection($customer->sales),
                'statistics' => [
                    'totalSales' => $totalSales,
                    'totalDebt' => $totalDebt,
                    'totalPurchases' => $customer->total_purchases,
                    'totalPaid' => $customer->total_paid,
                ]
            ]
        );
    }

    /**
     * Delete a customer
     */
    public function deleteCustomer(Shop $shop, Customer $customer): JsonResponse
    {
        $this->initRequestTime();

        // Authorization
        $this->authorize('delete', $customer);

        if ($customer->shop_id !== $shop->id) {
            return $this->errorResponse(
                'Customer not found in this shop.',
                null,
                Response::HTTP_NOT_FOUND
            );
        }

        if ($customer->current_debt > 0) {
            return $this->errorResponse(
                'Cannot delete customer with outstanding debt.',
                ['currentDebt' => $customer->current_debt],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $customer->delete();

        return $this->successResponse('Customer deleted successfully.');
    }
}

