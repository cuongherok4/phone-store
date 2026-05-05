<?php
// app/Http/Requests/Admin/StoreVariantRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVariantRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $variantId = $this->route('id'); // null khi create

        return [
            'sku'           => "required|string|max:100|unique:product_variants,sku,{$variantId}",
            'price'         => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost_price'    => 'nullable|numeric|min:0',
            'is_active'     => 'boolean',
            // attributes: mảng [attribute_id => attribute_value_id]
            'attributes'    => 'nullable|array',
            'attributes.*'  => 'nullable|exists:attribute_values,id',
            // ảnh upload
            'images'        => 'nullable|array',
            'images.*'      => 'image|max:5120', // 5MB
            'primary_image' => 'nullable|integer', // index trong mảng images
        ];
    }

    public function attributes(): array
    {
        return [
            'sku'   => 'SKU',
            'price' => 'giá bán',
        ];
    }
}