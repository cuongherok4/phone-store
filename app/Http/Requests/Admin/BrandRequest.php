<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('thuong_hieu')?->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['nullable', 'string', 'max:255', 'unique:brands,slug,' . $brandId],
            'logo'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'slug.unique'   => 'Slug này đã được sử dụng.',
            'logo.image'    => 'File phải là hình ảnh.',
            'logo.mimes'    => 'Chỉ chấp nhận: jpeg, png, jpg, webp.',
            'logo.max'      => 'Kích thước ảnh tối đa 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->slug) && !empty($this->name)) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }
}