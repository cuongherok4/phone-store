<?php
// app/Http/Requests/Admin/StoreProductRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $productId = $this->route('id'); // null khi create

        return [
            'name'        => 'required|string|max:255',
            'slug'        => "required|string|max:255|unique:products,slug,{$productId}",
            'brand_id'    => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'short_desc'  => 'nullable|string|max:500',
            'status'      => 'required|in:0,1',
            // Thêm validation cho thông số kỹ thuật
            'specifications' => 'nullable|array',
            'specifications.screen' => 'nullable|string|max:255',
            'specifications.camera_rear' => 'nullable|string|max:255',
            'specifications.camera_front' => 'nullable|string|max:255',
            'specifications.chipset' => 'nullable|string|max:255',
            'specifications.battery_charging' => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'tên sản phẩm',
            'slug'        => 'slug',
            'brand_id'    => 'thương hiệu',
            'status'      => 'trạng thái',
            'specifications.screen' => 'thông số màn hình',
            'specifications.camera_rear' => 'thông số camera sau',
            'specifications.camera_front' => 'thông số camera trước',
            'specifications.chipset' => 'thông số chipset/CPU',
            'specifications.battery_charging' => 'thông số pin & sạc',
        ];
    }
}