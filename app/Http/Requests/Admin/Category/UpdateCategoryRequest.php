<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:categories,slug,' . $this->route('danh_muc'),
            'parent_id'  => 'nullable|exists:categories,id',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'boolean',
        ];
    }
}