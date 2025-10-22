<?php

namespace App\Http\Controllers\Api;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrder\CreatePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\RecordPaymentRequest;
use App\Http\Requests\PurchaseOrder\TransferStockRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderStatusRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\PurchasePaymentResource;
use App\Http\Resources\StockTransferResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchasePayment;
use App\Models\Shop;
use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders for the buyer shop.
     */
    public function indexAsBuyer(Request $request, Shop $shop): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        $orders = PurchaseOrder::where('buyer_shop_id', $shop->id)
            ->with(['sellerShop', 'items.product', 'approvedBy'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->seller_shop_id, function ($query, $sellerShopId) {
                $query->where('seller_shop_id', $sellerShopId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                      ->orWhereHas('sellerShop', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->withCount('items')
            ->latest()
            ->paginate($request->per_page ?? 15);

         return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'orders' => PurchaseOrderResource::collection($orders),
                'pagination' => [
                    'total' => $orders->total(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                ]
            ]
        ]);
    }

    /**
     * Display a listing of purchase orders for the seller shop.
     */
    public function indexAsSeller(Request $request, Shop $shop): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        $orders = PurchaseOrder::where('seller_shop_id', $shop->id)
            ->with(['buyerShop', 'items.product', 'approvedBy'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->buyer_shop_id, function ($query, $buyerShopId) {
                $query->where('buyer_shop_id', $buyerShopId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                      ->orWhereHas('buyerShop', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->withCount('items')
            ->latest()
            ->paginate($request->per_page ?? 15);

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'data' => [
                'orders' => PurchaseOrderResource::collection($orders),
                'pagination' => [
                    'total' => $orders->total(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                ]
            ]
        ]);
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(CreatePurchaseOrderRequest $request, Shop $shop): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        // Verify seller shop exists and is active
        $sellerShop = Shop::where('id', $request->seller_shop_id)
            ->where('is_active', true)
            ->first();

        if (!$sellerShop) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Seller shop not found or inactive.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'buyer_shop_id' => $shop->id,
                'seller_shop_id' => $request->seller_shop_id,
                'status' => PurchaseOrderStatus::PENDING,
                'total_amount' => $totalAmount,
                'total_paid' => 0,
                'notes' => $request->notes,
            ]);

            // Create purchase order items
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $purchaseOrder->load(['sellerShop', 'items.product']);

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_CREATED,
                'message' => 'Purchase order created successfully',
                'data' => [
                    'purchaseOrder' => new PurchaseOrderResource($purchaseOrder),
                ],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(Request $request, Shop $shop, PurchaseOrder $purchaseOrder): JsonResponse
    {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        // Check if shop is involved in this purchase order
        if ($purchaseOrder->buyer_shop_id !== $shop->id && 
            $purchaseOrder->seller_shop_id !== $shop->id) {
            return new JsonResponse([
                'message' => 'Purchase order not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $purchaseOrder->load([
            'buyerShop',
            'sellerShop',
            'items.product',
            'payments.recordedBy',
            'stockTransfers.product',
            'approvedBy'
        ]);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'purchaseOrder' => new PurchaseOrderResource($purchaseOrder),
            ],
        ]);
    }

    /**
     * Update the specified purchase order.
     */
    public function update(
        UpdatePurchaseOrderRequest $request, 
        Shop $shop, 
        PurchaseOrder $purchaseOrder
    ): JsonResponse {
        // Verify user has access to this shop
        // if (!$shop->hasAccess($request->user())) {
        //     abort(403, 'You do not have access to this shop.');
        // }

        // Only buyer can update and only if pending
        if ($purchaseOrder->buyer_shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Only the buyer shop can update this purchase order.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($purchaseOrder->status !== PurchaseOrderStatus::PENDING) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Only pending purchase orders can be updated.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            // Update notes if provided
            if ($request->has('notes')) {
                $purchaseOrder->notes = $request->notes;
            }

            // Update items if provided
            if ($request->has('items')) {
                // Delete existing items
                $purchaseOrder->items()->delete();

                // Calculate new total amount
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $totalAmount += $item['quantity'] * $item['unit_price'];
                    
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                $purchaseOrder->total_amount = $totalAmount;
            }

            $purchaseOrder->save();

            DB::commit();

            $purchaseOrder->load(['sellerShop', 'items.product']);

            return new JsonResponse([
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'data' => [
                    'purchaseOrder' => new PurchaseOrderResource($purchaseOrder),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update purchase order',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update purchase order status (approve/reject/cancel/complete).
     */
    public function updateStatus(
        UpdatePurchaseOrderStatusRequest $request,
        Shop $shop,
        PurchaseOrder $purchaseOrder
    ): JsonResponse {
        // Verify user has access to this shop
        if (!$shop->hasAccess($request->user())) {
            abort(403, 'You do not have access to this shop.');
        }

        $newStatus = PurchaseOrderStatus::from($request->status);

        // Validate status transition
        if (!$purchaseOrder->status->canTransitionTo($newStatus)) {
            return new JsonResponse([
                'success' => false,
                'message' => "Cannot transition from {$purchaseOrder->status->label()} to {$newStatus->label()}.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Only seller can approve/reject
        if (in_array($newStatus, [PurchaseOrderStatus::APPROVED, PurchaseOrderStatus::REJECTED])) {
            if ($purchaseOrder->seller_shop_id !== $shop->id) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Only the seller shop can approve or reject this purchase order.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        // Both parties can cancel
        if ($newStatus === PurchaseOrderStatus::CANCELLED) {
            if ($purchaseOrder->buyer_shop_id !== $shop->id && 
                $purchaseOrder->seller_shop_id !== $shop->id) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'You cannot cancel this purchase order.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        // Only buyer can mark as completed
        if ($newStatus === PurchaseOrderStatus::COMPLETED) {
            if ($purchaseOrder->buyer_shop_id !== $shop->id) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Only the buyer shop can complete this purchase order.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $purchaseOrder->status = $newStatus;
        
        if ($newStatus === PurchaseOrderStatus::APPROVED) {
            $purchaseOrder->approved_at = now();
            $purchaseOrder->approved_by = $request->user()->id;
        }

        if ($request->notes) {
            $purchaseOrder->notes = $purchaseOrder->notes 
                ? $purchaseOrder->notes . "\n\n" . $request->notes 
                : $request->notes;
        }

        $purchaseOrder->save();

        $purchaseOrder->load(['buyerShop', 'sellerShop', 'approvedBy']);

        return new JsonResponse([
            'success' => true,
            'message' => "Purchase order {$newStatus->label()} successfully",
            'data' => [
                'purchaseOrder' => new PurchaseOrderResource($purchaseOrder),
            ],
        ]);
    }

    /**
     * Record a payment for the purchase order.
     */
    public function recordPayment(
        RecordPaymentRequest $request,
        Shop $shop,
        PurchaseOrder $purchaseOrder
    ): JsonResponse {
        // Verify user has access to this shop
        if (!$shop->hasAccess($request->user())) {
            abort(403, 'You do not have access to this shop.');
        }

        // Only buyer can record payments
        if ($purchaseOrder->buyer_shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Only the buyer shop can record payments.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if order is approved
        if (!in_array($purchaseOrder->status, [
            PurchaseOrderStatus::APPROVED,
            PurchaseOrderStatus::COMPLETED
        ])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Can only record payments for approved purchase orders.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if payment exceeds remaining balance
        $remainingBalance = $purchaseOrder->remaining_balance;
        if ($request->amount > $remainingBalance) {
            return new JsonResponse([
                'success' => false,
                'message' => "Payment amount cannot exceed remaining balance of {$remainingBalance}.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            $payment = PurchasePayment::create([
                'purchase_order_id' => $purchaseOrder->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'recorded_by' => $request->user()->id,
            ]);

            // Update total paid
            $purchaseOrder->total_paid += $request->amount;
            $purchaseOrder->save();

            DB::commit();

            $payment->load('recordedBy');

            return new JsonResponse([
                'success' => true,
                'code' => Response::HTTP_CREATED,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'payment' => new PurchasePaymentResource($payment),
                    'purchaseOrder' => [
                        'totalPaid' => $purchaseOrder->total_paid,
                        'remainingBalance' => $purchaseOrder->remaining_balance,
                        'isFullyPaid' => $purchaseOrder->isFullyPaid(),
                    ],
                ],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to record payment',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Transfer stock from seller to buyer.
     */
    public function transferStock(
        TransferStockRequest $request,
        Shop $shop,
        PurchaseOrder $purchaseOrder
    ): JsonResponse {
        // Verify user has access to this shop
        if (!$shop->hasAccess($request->user())) {
            abort(403, 'You do not have access to this shop.');
        }

        // Only seller can transfer stock
        if ($purchaseOrder->seller_shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Only the seller shop can transfer stock.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if order is approved
        if ($purchaseOrder->status !== PurchaseOrderStatus::APPROVED) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Can only transfer stock for approved purchase orders.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            $transfers = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Verify product belongs to seller shop
                if ($product->shop_id !== $shop->id) {
                    throw new \Exception("Product {$product->product_name} does not belong to your shop.");
                }

                // Check if seller has enough stock
                if ($product->current_stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product {$product->product_name}. Available: {$product->current_stock}, Requested: {$item['quantity']}");
                }

                // Reduce seller's stock
                $product->current_stock -= $item['quantity'];
                $product->save();

                // Create or update product in buyer's shop
                $buyerProduct = Product::where('shop_id', $purchaseOrder->buyer_shop_id)
                    ->where('product_name', $product->product_name)
                    ->where('sku', $product->sku)
                    ->first();

                if ($buyerProduct) {
                    // Update existing product
                    $buyerProduct->current_stock += $item['quantity'];
                    $buyerProduct->save();
                } else {
                    // Create new product in buyer's shop
                    $newProduct = $product->replicate();
                    $newProduct->shop_id = $purchaseOrder->buyer_shop_id;
                    $newProduct->current_stock = $item['quantity'];
                    $newProduct->save();
                }

                // Record stock transfer
                $transfer = StockTransfer::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'transferred_at' => now(),
                    'transferred_by' => $request->user()->id,
                    'notes' => $item['notes'] ?? null,
                ]);

                $transfers[] = $transfer;
            }

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Stock transferred successfully',
                'data' => [
                    'transfers' => StockTransferResource::collection(collect($transfers)->load(['product', 'transferredBy'])),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to transfer stock',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a purchase order.
     */
    public function destroy(Request $request, Shop $shop, PurchaseOrder $purchaseOrder): JsonResponse
    {
        // Verify user has access to this shop
        if (!$shop->hasAccess($request->user())) {
            abort(403, 'You do not have access to this shop.');
        }

        // Only buyer can delete and only if pending
        if ($purchaseOrder->buyer_shop_id !== $shop->id) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Only the buyer shop can delete this purchase order.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($purchaseOrder->status !== PurchaseOrderStatus::PENDING) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Only pending purchase orders can be deleted.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $purchaseOrder->delete();

        return new JsonResponse([
            'success' => true,
            'message' => 'Purchase order deleted successfully',
        ]);
    }
}