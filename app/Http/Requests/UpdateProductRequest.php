<?php

namespace App\Http\Requests;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'category_id' => 'sometimes|nullable|uuid|exists:categories,id',
            'sku' => 'sometimes|required|string|max:100',
            'barcode' => 'sometimes|nullable|string|max:100',
            'unit_type' => ['sometimes', 'required', new Enum(UnitType::class)],
            'purchase_quantity' => 'sometimes|required|numeric|min:0',
            'total_amount_paid' => 'sometimes|required|numeric|min:0',
            'price_per_unit' => 'sometimes|nullable|numeric|min:0',
            'sell_individual_items' => 'sometimes|boolean',
            'break_down_count_per_unit' => 'sometimes|nullable|required_if:sell_individual_items,true|integer|min:1',
            'price_per_item' => 'sometimes|nullable|required_if:sell_individual_items,true|numeric|min:0',
            'low_stock_threshold' => 'sometimes|nullable|integer|min:0',
            'notes' => 'sometimes|nullable|string'
        ];
    }
}