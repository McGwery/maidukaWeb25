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
use App\Traits\HasStandardResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PurchaseOrderController extends Controller
{
    use HasStandardResponse;

    /**
     * Display a listing of purchase orders for the buyer shop.
     */
    public function indexAsBuyer(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('viewAny', [PurchaseOrder::class, $shop]);

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

        $transformedOrders = $orders->setCollection(collect(PurchaseOrderResource::collection($orders->getCollection())));

        return $this->paginatedResponse(
            'Purchase orders retrieved successfully.',
            $transformedOrders
        );
    }

    /**
     * Display a listing of purchase orders for the seller shop.
     */
    public function indexAsSeller(Request $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('viewAny', [PurchaseOrder::class, $shop]);

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

        $transformedOrders = $orders->setCollection(collect(PurchaseOrderResource::collection($orders->getCollection())));

        return $this->paginatedResponse(
            'Purchase orders retrieved successfully.',
            $transformedOrders
        );
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(CreatePurchaseOrderRequest $request, Shop $shop): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('create', [PurchaseOrder::class, $shop]);

        // Verify seller shop exists and is active
        $sellerShop = Shop::where('id', $request->seller_shop_id)
            ->where('is_active', true)
            ->first();

        if (!$sellerShop) {
            return $this->errorResponse(
                'Seller shop not found or inactive.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        DB::beginTransaction();
        try {
            // Calculate total amount
            $totalAmount = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'buyer_shop_id' => $shop->id,
                'seller_shop_id' => $request->seller_shop_id,
                'status' => PurchaseOrderStatus::PENDING,
                'total_amount' => $totalAmount,
                'total_paid' => 0,
                'notes' => $request->notes,
                'is_internal' => $request->is_internal,
            ]);

            // Create purchase order items
            $purchaseOrder->items()->createMany($request->items);

            DB::commit();

            $purchaseOrder->load(['sellerShop', 'items.product']);

            return $this->successResponse(
                'Purchase order created successfully.',
                ['purchaseOrder' => new PurchaseOrderResource($purchaseOrder)],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to create purchase order.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(Request $request, Shop $shop, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('view', [$purchaseOrder, $shop]);

        $purchaseOrder->load(['buyerShop', 'sellerShop', 'items.product', 'payments', 'stockTransfers']);

        return $this->successResponse(
            'Purchase order retrieved successfully.',
            ['purchaseOrder' => new PurchaseOrderResource($purchaseOrder)]
        );
    }

    /**
     * Update the specified purchase order.
     */
    public function update(UpdatePurchaseOrderRequest $request, Shop $shop, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->initRequestTime();

        $this->authorize('update', [$purchaseOrder, $shop]);

        DB::beginTransaction();
        try {
            if ($request->has('items')) {
                // Delete existing items
                $purchaseOrder->items()->delete();

                // Create new items
                $purchaseOrder->items()->createMany($request->items);

                // Update total amount
                $totalAmount = collect($request->items)->sum(function ($item) {
                    return $item['quantity'] * $item['unit_price'];
                });

                $purchaseOrder->update([
                    'total_amount' => $totalAmount,
                    'notes' => $request->notes ?? $purchaseOrder->notes,
                ]);
            } else {
                $purchaseOrder->update([
                    'notes' => $request->notes,
                ]);
            }

            DB::commit();

            $purchaseOrder->load(['sellerShop', 'items.product']);

            return $this->successResponse(
                'Purchase order updated successfully.',
                ['purchaseOrder' => new PurchaseOrderResource($purchaseOrder)]
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to update purchase order.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
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
        $this->initRequestTime();

        $this->authorize('approve', [$purchaseOrder, $shop]);

        if ($request->status === PurchaseOrderStatus::APPROVED && !$purchaseOrder->canBeApproved()) {
            return $this->errorResponse(
                'Purchase order cannot be approved.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($request->status === PurchaseOrderStatus::COMPLETED && !$purchaseOrder->canBeCompleted()) {
            return $this->errorResponse(
                'Purchase order cannot be completed.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($request->status === PurchaseOrderStatus::CANCELLED && !$purchaseOrder->canBeCancelled()) {
            return $this->errorResponse(
                'Purchase order cannot be cancelled.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->update([
                'status' => $request->status,
                'approved_at' => $request->status === PurchaseOrderStatus::APPROVED ? now() : null,
                'approved_by' => $request->status === PurchaseOrderStatus::APPROVED ? auth()->id() : null,
            ]);

            DB::commit();

            return $this->successResponse(
                'Purchase order status updated successfully.',
                ['purchaseOrder' => new PurchaseOrderResource($purchaseOrder)]
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to update purchase order status.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Record a payment for the purchase order.
     */
    public function recordPayment(
        RecordPaymentRequest $request,
        Shop $shop,
        PurchaseOrder $purchaseOrder
    ): JsonResponse {
        $this->initRequestTime();

        $this->authorize('recordPayment', [$purchaseOrder, $shop]);

        DB::beginTransaction();
        try {
            $payment = PurchasePayment::create([
                'purchase_order_id' => $purchaseOrder->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            $purchaseOrder->increment('total_paid', $request->amount);

            DB::commit();

            return $this->successResponse(
                'Payment recorded successfully.',
                ['payment' => new PurchasePaymentResource($payment)],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to record payment.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
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
        $this->initRequestTime();

        $this->authorize('transferStock', [$purchaseOrder, $shop]);

        DB::beginTransaction();
        try {
            $stockTransfer = StockTransfer::create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
            ]);

            // Update product stock
            $product = Product::findOrFail($request->product_id);
            $product->increment('stock_quantity', $request->quantity);

            DB::commit();

            return $this->successResponse(
                'Stock transferred successfully.',
                ['stockTransfer' => new StockTransferResource($stockTransfer)],
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Failed to transfer stock.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
