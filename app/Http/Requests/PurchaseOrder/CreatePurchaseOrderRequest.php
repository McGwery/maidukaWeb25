<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class CreatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_internal' => ['required', 'boolean'],
            'seller_shop_id' => [
                'required_if:is_internal,false',
                'uuid',
                'exists:shops,id',
                'different:buyer_shop_id',
                function ($attribute, $value, $fail) {
                    if (!$this->is_internal && $value) {
                        $buyerShop = $this->user()->currentShop;
                        $hasRelationship = $buyerShop->suppliers()->where('id', $value)->exists();

                        if (!$hasRelationship) {
                            $fail('The selected seller must be an existing supplier in your shop.');
                        }
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'uuid',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    $buyerShop = $this->user()->currentShop;

                    // For internal purchases, validate that products belong to buyer's shop
                    if ($this->is_internal) {
                        $exists = Product::where('id', $value)
                            ->where('shop_id', $buyerShop->id)
                            ->exists();

                        if (!$exists) {
                            $fail('For internal purchases, products must belong to your shop.');
                        }
                    }
                },
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'seller_shop_id.different' => 'Cannot create purchase order with your own shop.',
            'seller_shop_id.required_if' => 'Seller shop is required for external purchases.',
            'items.required' => 'At least one item is required.',
            'items.*.product_id.exists' => 'One or more products do not exist.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->is_internal) {
            $this->merge([
                'seller_shop_id' => $this->user()->currentShop->id,
            ]);
        }
    }
}
