<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Shop;
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
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
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
                    'suggested_low_stock_threshold' => ceil($data['purchase_quantity'] * 0.2),
                    'suggested_price_per_item' => $data['sell_individual_items'] && isset($data['break_down_count_per_unit'])
                        ? round($data['total_amount_paid'] / ($data['purchase_quantity'] * $data['break_down_count_per_unit']), 2)
                        : null,
                    'total_individual_items' => $data['sell_individual_items'] && isset($data['break_down_count_per_unit'])
                        ? ($data['purchase_quantity'] * $data['break_down_count_per_unit'])
                        : null,
                    'cost_per_unit' => $data['cost_per_unit'],
                    'profit_margin_per_unit' => isset($data['price_per_unit'])
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
                    'profit_margin_per_unit' => $product->price_per_unit
                        ? round((($product->price_per_unit - $product->cost_per_unit) / $product->cost_per_unit) * 100, 2)
                        : null,
                    'total_individual_items' => $product->sell_individual_items && $product->break_down_count_per_unit
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
                    'suggested_low_stock_threshold' => ceil($product->purchase_quantity * 0.2),
                    'suggested_price_per_item' => $product->sell_individual_items && $product->break_down_count_per_unit
                        ? round($product->total_amount_paid / ($product->purchase_quantity * $product->break_down_count_per_unit), 2)
                        : null,
                    'total_individual_items' => $product->sell_individual_items && $product->break_down_count_per_unit
                        ? ($product->current_stock * $product->break_down_count_per_unit)
                        : null,
                    'cost_per_unit' => $product->cost_per_unit,
                    'profit_margin_per_unit' => $product->price_per_unit
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
     * Update product stock.
     */
    public function updateStock(Request $request, Shop $shop, Product $product): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        // Check if product belongs to this shop
        if ($product->shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_NO_CONTENT,
                'message' => 'Product not found in this shop.'
            ], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'adjustment' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255'
        ]);

        $oldStock = $product->current_stock;
        $newStock = $oldStock + $request->adjustment;

        if ($newStock < 0) {
            return new JsonResponse([
                'message' => 'Stock cannot be reduced below zero'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $product->update([
            'current_stock' => $newStock
        ]);

        // TODO: You might want to log this stock adjustment in a separate table
        // StockAdjustment::create([...]);

        return new JsonResponse([
            'success' => true,
            'message' => 'Stock updated successfully',
            'code' => Response::HTTP_NO_CONTENT,
            'data' => [
                'product' => new ProductResource($product),
                'stock_change' => [
                    'old_stock' => $oldStock,
                    'adjustment' => $request->adjustment,
                    'new_stock' => $newStock,
                    'reason' => $request->reason
                ]
            ]
        ]);
    }
}
