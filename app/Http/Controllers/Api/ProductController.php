<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockAdjustmentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\StockAdjustmentRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StockAdjustmentResource;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockAdjustment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of products for the specified shop.
     */
    public function index(Request $request, Shop $shop): JsonResponse
    {

        $products = $shop->products()
            ->with(['category'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->low_stock, function ($query) {
                $query->whereColumn('current_stock', '<=', 'low_stock_threshold');
            })
            ->when($request->sort_by && $request->sort_direction, function ($query) use ($request) {
                $query->orderBy($request->sort_by, $request->sort_direction);
            }, function ($query) {
                $query->latest();
            })
            ->paginate($request->per_page ?? 15);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'products' => ProductResource::collection($products),
                'pagination' => [
                    'total' => $products->total(),
                    'currentPage' => $products->currentPage(),
                    'lastPage' => $products->lastPage(),
                    'perPage' => $products->perPage(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(CreateProductRequest $request, Shop $shop): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        $data = $request->validated();

        // Set the shop_id
        $data['shop_id'] = $shop->id;

        // Calculate cost per unit
        $data['cost_per_unit'] = round($data['total_amount_paid'] / $data['purchase_quantity'], 2);

        // Set initial stock
        $data['current_stock'] = $data['purchase_quantity'];

        // Calculate low stock threshold if not provided (20% rule)
        if (!isset($data['low_stock_threshold'])) {
            $data['low_stock_threshold'] = ceil($data['purchase_quantity'] * 0.2);
        }

        // Calculate price per item if selling individual items and price not provided
        if ($data['sell_individual_items'] && !isset($data['price_per_item']) && isset($data['break_down_count_per_unit'])) {
            $totalItems = $data['purchase_quantity'] * $data['break_down_count_per_unit'];
            $data['price_per_item'] = round($data['total_amount_paid'] / $totalItems, 2);
        }

        $product = Product::create($data);

        // Load relationships for the response
        $product->load(['category', 'shop']);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_CREATED,
            'message' => 'Product added successfully',
            'data' => [
                'product' => new ProductResource($product),
                'computed' => [
                    'suggestedLowStockThreshold' => ceil($data['purchase_quantity'] * 0.2),
                    'suggestedPricePerItem' => $data['sell_individual_items'] && isset($data['break_down_count_per_unit'])
                        ? round($data['total_amount_paid'] / ($data['purchase_quantity'] * $data['break_down_count_per_unit']), 2)
                        : null,
                    'totalIndividualItems' => $data['sell_individual_items'] && isset($data['break_down_count_per_unit'])
                        ? ($data['purchase_quantity'] * $data['break_down_count_per_unit'])
                        : null,
                    'costPerUnit' => $data['cost_per_unit'],
                    'profitMarginPerUnit' => isset($data['price_per_unit'])
                        ? round((($data['price_per_unit'] - $data['cost_per_unit']) / $data['cost_per_unit']) * 100, 2)
                        : null,
                ]
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified product.
     */
    public function show(Request $request, Shop $shop, Product $product): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        // Check if product belongs to this shop
        if ($product->shop_id !== $shop->id) {
            return new JsonResponse([
                'message' => 'Product not found in this shop.'
            ], Response::HTTP_NOT_FOUND);
        }

        $product->load(['category', 'shop']);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'product' => new ProductResource($product),
                'computed' => [
                    'profitMarginPerUnit' => $product->price_per_unit
                        ? round((($product->price_per_unit - $product->cost_per_unit) / $product->cost_per_unit) * 100, 2)
                        : null,
                    'totalIndividualItems' => $product->sell_individual_items && $product->break_down_count_per_unit
                        ? ($product->current_stock * $product->break_down_count_per_unit)
                        : null,
                ]
            ]
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Shop $shop, Product $product): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        // Check if product belongs to this shop
        if ($product->shop_id !== $shop->id) {
            return new JsonResponse([
                'message' => 'Product not found in this shop.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        // Recalculate cost per unit if total amount or purchase quantity changes
        if (isset($data['total_amount_paid']) || isset($data['purchase_quantity'])) {
            $totalAmount = $data['total_amount_paid'] ?? $product->total_amount_paid;
            $purchaseQuantity = $data['purchase_quantity'] ?? $product->purchase_quantity;
            $data['cost_per_unit'] = round($totalAmount / $purchaseQuantity, 2);
        }

        // Update low stock threshold if purchase quantity changes and threshold was using default
        if (isset($data['purchase_quantity']) && $product->low_stock_threshold === ceil($product->purchase_quantity * 0.2)) {
            $data['low_stock_threshold'] = ceil($data['purchase_quantity'] * 0.2);
        }

        // Recalculate price per item if selling individual items and related fields change
        if (
            $product->sell_individual_items &&
            (isset($data['total_amount_paid']) || isset($data['purchase_quantity']) || isset($data['break_down_count_per_unit']))
        ) {
            $totalAmount = $data['total_amount_paid'] ?? $product->total_amount_paid;
            $purchaseQuantity = $data['purchase_quantity'] ?? $product->purchase_quantity;
            $breakDownCount = $data['break_down_count_per_unit'] ?? $product->break_down_count_per_unit;

            if (!isset($data['price_per_item']) && $breakDownCount) {
                $totalItems = $purchaseQuantity * $breakDownCount;
                $data['price_per_item'] = round($totalAmount / $totalItems, 2);
            }
        }
        $product->update($data);

        // Reload relationships for the response
        $product->load(['category', 'shop']);

        return new JsonResponse([
            'success' => true,
            'message' => 'Product updated successfully',
            'code' => Response::HTTP_OK,
            'data' => [
                'product' => new ProductResource($product),
                'computed' => [
                    'suggestedLowStockThreshold' => ceil($product->purchase_quantity * 0.2),
                    'suggestedPricePerItem' => $product->sell_individual_items && $product->break_down_count_per_unit
                        ? round($product->total_amount_paid / ($product->purchase_quantity * $product->break_down_count_per_unit), 2)
                        : null,
                    'totalIndividualItems' => $product->sell_individual_items && $product->break_down_count_per_unit
                        ? ($product->current_stock * $product->break_down_count_per_unit)
                        : null,
                    'costPerUnit' => $product->cost_per_unit,
                    'profitMarginPerUnit' => $product->price_per_unit
                        ? round((($product->price_per_unit - $product->cost_per_unit) / $product->cost_per_unit) * 100, 2)
                        : null,
                ]
            ]
        ]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Request $request, Shop $shop, Product $product): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        // Check if product belongs to this shop
        if ($product->shop_id !== $shop->id) {
            return new JsonResponse([
                 'success' => false,
                'message' => 'Product not found in this shop.'
            ], Response::HTTP_NOT_FOUND);
        }

        $product->delete();

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Update product stock with tracking.
     */
    public function updateStock(StockAdjustmentRequest $request, Shop $shop, Product $product): JsonResponse
    {
        // Check if product belongs to this shop
        if ($product->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Product not found in this shop.'
            ], Response::HTTP_NOT_FOUND);
        }

        $oldStock = $product->current_stock;
        $quantity = $request->quantity;
        $newStock = $oldStock + $quantity;

        if ($newStock < 0) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Stock cannot be reduced below zero. Current stock: ' . $oldStock
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Create stock adjustment record
        $adjustment = StockAdjustment::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'quantity' => $quantity,
            'value_at_time' => $product->cost_per_unit,
            'previous_stock' => $oldStock,
            'new_stock' => $newStock,
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);

        // Update product stock
        $product->update([
            'current_stock' => $newStock
        ]);

        return new JsonResponse([
            'success' => true,
            'message' => 'Stock adjusted successfully',
            'code' => Response::HTTP_OK,
            'data' => [
                'product' => new ProductResource($product->fresh()),
                'adjustment' => new StockAdjustmentResource($adjustment),
            ]
        ]);
    }

    /**
     * Get stock adjustment history for a product.
     */
    public function stockAdjustmentHistory(Request $request, Shop $shop, Product $product): JsonResponse
    {
        // Check if product belongs to this shop
        if ($product->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Product not found in this shop.'
            ], Response::HTTP_NOT_FOUND);
        }

        $adjustments = $product->stockAdjustments()
            ->with(['user'])
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->from_date, function ($query, $fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($query, $toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        $totalLosses = $product->stockAdjustments()
            ->whereIn('type', [
                StockAdjustmentType::DAMAGED->value,
                StockAdjustmentType::EXPIRED->value,
                StockAdjustmentType::LOST->value,
                StockAdjustmentType::THEFT->value,
            ])
            ->get()
            ->sum(fn($adj) => $adj->getMonetaryImpact());

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'adjustments' => StockAdjustmentResource::collection($adjustments),
                'summary' => [
                    'totalReductions' => $product->stockAdjustments()->where('quantity', '<', 0)->sum('quantity'),
                    'totalAdditions' => $product->stockAdjustments()->where('quantity', '>', 0)->sum('quantity'),
                    'totalLossesValue' => $totalLosses,
                ],
                'pagination' => [
                    'total' => $adjustments->total(),
                    'currentPage' => $adjustments->currentPage(),
                    'lastPage' => $adjustments->lastPage(),
                    'perPage' => $adjustments->perPage(),
                ]
            ]
        ]);
    }

    /**
     * Get inventory value and profit analysis for shop.
     */
    public function inventoryAnalysis(Request $request, Shop $shop): JsonResponse
    {
        $products = $shop->products()
            ->with(['stockAdjustments' => function ($query) {
                $query->whereIn('type', [
                    StockAdjustmentType::DAMAGED->value,
                    StockAdjustmentType::EXPIRED->value,
                    StockAdjustmentType::LOST->value,
                    StockAdjustmentType::THEFT->value,
                ]);
            }])
            ->where('current_stock', '>', 0)
            ->get();

        $totalInventoryValue = 0;
        $totalExpectedRevenue = 0;
        $totalExpectedProfit = 0;
        $totalLosses = 0;
        $productAnalysis = [];

        foreach ($products as $product) {
            $inventoryValue = $product->getInventoryValue();
            $expectedRevenue = $product->getExpectedRevenue();
            $expectedProfit = $product->getExpectedProfit();
            $losses = $product->getTotalLosses();

            $totalInventoryValue += $inventoryValue;
            $totalExpectedRevenue += $expectedRevenue;
            $totalExpectedProfit += $expectedProfit;
            $totalLosses += $losses;

            if ($request->include_products) {
                $productAnalysis[] = [
                    'productId' => $product->id,
                    'productName' => $product->product_name,
                    'currentStock' => $product->current_stock,
                    'costPerUnit' => $product->cost_per_unit,
                    'inventoryValue' => round($inventoryValue, 2),
                    'expectedRevenue' => round($expectedRevenue, 2),
                    'expectedProfit' => round($expectedProfit, 2),
                    'expectedProfitMargin' => round($product->getExpectedProfitMargin(), 2),
                    'totalLosses' => round($losses, 2),
                ];
            }
        }

        $profitMarginPercentage = $totalInventoryValue > 0
            ? ($totalExpectedProfit / $totalInventoryValue) * 100
            : 0;

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'summary' => [
                    'totalInventoryValue' => round($totalInventoryValue, 2),
                    'totalExpectedRevenue' => round($totalExpectedRevenue, 2),
                    'totalExpectedProfit' => round($totalExpectedProfit, 2),
                    'overallProfitMarginPercentage' => round($profitMarginPercentage, 2),
                    'totalLosses' => round($totalLosses, 2),
                    'netExpectedProfit' => round($totalExpectedProfit - $totalLosses, 2),
                    'productsCount' => $products->count(),
                    'lowStockCount' => $products->filter(fn($p) => $p->isLowStock())->count(),
                ],
                'products' => $request->include_products ? $productAnalysis : null,
            ]
        ]);
    }

    /**
     * Get shop-wide stock adjustment summary.
     */
    public function adjustmentsSummary(Request $request, Shop $shop): JsonResponse
    {
        $query = StockAdjustment::query()
            ->whereHas('product', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id);
            })
            ->with(['product', 'user']);

        // Apply date filters
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $adjustments = $query->latest()->paginate($request->per_page ?? 15);

        // Get summary by type
        $summaryByType = [];
        foreach (StockAdjustmentType::cases() as $type) {
            $typeAdjustments = StockAdjustment::query()
                ->whereHas('product', function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                })
                ->where('type', $type->value)
                ->when($request->from_date, function ($q, $date) {
                    $q->whereDate('created_at', '>=', $date);
                })
                ->when($request->to_date, function ($q, $date) {
                    $q->whereDate('created_at', '<=', $date);
                })
                ->get();

            $summaryByType[$type->value] = [
                'label' => $type->label(),
                'count' => $typeAdjustments->count(),
                'totalQuantity' => $typeAdjustments->sum('quantity'),
                'totalValue' => round($typeAdjustments->sum(fn($adj) => $adj->getMonetaryImpact()), 2),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'adjustments' => StockAdjustmentResource::collection($adjustments),
                'summaryByType' => $summaryByType,
                'pagination' => [
                    'total' => $adjustments->total(),
                    'currentPage' => $adjustments->currentPage(),
                    'lastPage' => $adjustments->lastPage(),
                    'perPage' => $adjustments->perPage(),
                ]
            ]
        ]);
    }
}
