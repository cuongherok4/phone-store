<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('danh_muc')?->id;

        return [
            'name'       => ['required', 'string', 'max:255'],
            'slug'       => ['nullable', 'string', 'max:255', 'unique:categories,slug,' . $categoryId],
            'parent_id'  => ['nullable', 'integer', 'exists:categories,id'],
            'image'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Tên danh mục không được để trống.',
            'name.max'         => 'Tên danh mục tối đa 255 ký tự.',
            'slug.unique'      => 'Slug này đã được sử dụng, vui lòng chọn slug khác.',
            'parent_id.exists' => 'Danh mục cha không hợp lệ.',
            'image.image'      => 'File phải là hình ảnh.',
            'image.mimes'      => 'Chỉ chấp nhận định dạng: jpeg, png, jpg, webp.',
            'image.max'        => 'Kích thước ảnh tối đa 2MB.',
            'sort_order.integer' => 'Thứ tự phải là số nguyên.',
            'sort_order.min'   => 'Thứ tự không được âm.',
        ];
    }

    /**
     * Tự tạo slug từ name nếu để trống, trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->slug) && !empty($this->name)) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }

        // Đảm bảo sort_order có giá trị mặc định
        if (is_null($this->sort_order)) {
            $this->merge(['sort_order' => 0]);
        }
    }
}