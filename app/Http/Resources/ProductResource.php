<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'productName' => $this->product_name,
            'description' => $this->description,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            
            // Purchase information
            'purchaseQuantity' => $this->purchase_quantity,
            'totalAmountPaid' => $this->total_amount_paid,
            'costPerUnit' => $this->cost_per_unit,
            
            // Unit configuration
            'unitType' => [
                'value' => $this->unit_type->value,
                'label' => $this->unit_type->label(),
            ],
            'breakDownCountPerUnit' => $this->break_down_count_per_unit,
            'smallItemName' => $this->small_item_name,
            
            // Selling configuration
            'sellWholeUnits' => $this->sell_whole_units,
            'pricePerUnit' => $this->price_per_unit,
            'sellIndividualItems' => $this->sell_individual_items,
            'pricePerItem' => $this->price_per_item,
            'sellInBundles' => $this->sell_in_bundles,
            
            // Stock information
            'currentStock' => $this->current_stock,
            'lowStockThreshold' => $this->low_stock_threshold,
            'isLowStock' => $this->isLowStock(),
            'totalIndividualItems' => $this->getTotalItems(),
            'trackInventory' => $this->track_inventory,
            
            // Media
            'imageUrl' => $this->image_url,
            
            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),
            'shop' => new ShopResource($this->whenLoaded('shop')),
            
            // Timestamps
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
        ];
    }
}