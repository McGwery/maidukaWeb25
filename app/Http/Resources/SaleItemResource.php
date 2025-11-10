<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'saleId' => $this->sale_id,
            'productId' => $this->product_id,
            'productName' => $this->product_name,
            'productSku' => $this->product_sku,
            'quantity' => $this->quantity,
            'unitType' => $this->unit_type,
            'originalPrice' => $this->original_price,
            'sellingPrice' => $this->selling_price,
            'costPrice' => $this->cost_price,
            'discountAmount' => $this->discount_amount,
            'discountPercentage' => $this->discount_percentage,
            'taxAmount' => $this->tax_amount,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'profit' => $this->profit,
            'createdAt' => $this->created_at,
        ];
    }
}

