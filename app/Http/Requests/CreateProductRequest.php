<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        $isService = $this->input('product_type') === ProductType::SERVICE->value;
        $isPhysical = $this->input('product_type') === ProductType::PHYSICAL->value || !$this->has('product_type');

        return [
            'product_type' => ['nullable', new Enum(ProductType::class)],
            'product_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],

            // Physical product fields (required only for physical products)
            'purchase_quantity' => [
                Rule::requiredIf($isPhysical),
                'nullable',
                'integer',
                'min:1'
            ],
            'unit_type' => [
                Rule::requiredIf($isPhysical),
                'nullable',
                new Enum(UnitType::class)
            ],
            'total_amount_paid' => [
                Rule::requiredIf($isPhysical),
                'nullable',
                'numeric',
                'min:0'
            ],

            // Service-specific fields
            'service_duration' => [
                Rule::requiredIf($isService),
                'nullable',
                'numeric',
                'min:0.1',
                'max:999.99'
            ],
            'hourly_rate' => [
                'nullable',
                'numeric',
                'min:0'
            ],

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
                Rule::requiredIf(function() use ($isService) {
                    return $this->sell_whole_units || $isService;
                }),
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
        $productType = $this->input('product_type', ProductType::PHYSICAL->value);
        $isService = $productType === ProductType::SERVICE->value;

        $this->merge([
            'product_type' => $productType,
            'sell_whole_units' => $this->boolean('sell_whole_units', !$isService),
            'sell_individual_items' => $this->boolean('sell_individual_items'),
            'sell_in_bundles' => $this->boolean('sell_in_bundles'),
            'track_inventory' => $this->boolean('track_inventory', !$isService),
        ]);
    }
}
