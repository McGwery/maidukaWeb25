<?php

namespace App\Http\Requests;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'purchase_quantity' => ['required', 'integer', 'min:1'],
            'unit_type' => ['required', new Enum(UnitType::class)],
            'total_amount_paid' => ['required', 'numeric', 'min:0'],
            
            // Optional product identifiers
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            
            // Unit breakdown configuration
            'break_down_count_per_unit' => [
                Rule::requiredIf($this->sell_individual_items),
                'nullable',
                'integer',
                'min:1'
            ],
            'small_item_name' => [
                Rule::requiredIf($this->sell_individual_items),
                'nullable',
                'string',
                'max:50'
            ],
            
            // Selling configuration
            'sell_whole_units' => ['boolean'],
            'price_per_unit' => [
                Rule::requiredIf($this->sell_whole_units),
                'nullable',
                'numeric',
                'min:0'
            ],
            'sell_individual_items' => ['boolean'],
            'price_per_item' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'sell_in_bundles' => ['boolean'],
            
            // Inventory management
            'low_stock_threshold' => ['nullable', 'integer', 'min:1'],
            'track_inventory' => ['boolean'],
            
            // Media
            'image_url' => ['nullable', 'url'],
            
            // Shop association
            'shop_id' => [
                'required', 
                'uuid', 
                Rule::exists('shops', 'id')->where(function ($query) {
                    $query->where('id', $this->shop_id)
                          ->where('is_active', true);
                })
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sell_whole_units' => $this->boolean('sell_whole_units'),
            'sell_individual_items' => $this->boolean('sell_individual_items'),
            'sell_in_bundles' => $this->boolean('sell_in_bundles'),
            'track_inventory' => $this->boolean('track_inventory'),
        ]);
    }
}