{{-- Partial: fields dùng trong cả form thêm mới và form sửa --}}
{{-- $v = ProductVariant|null --}}

{{-- SKU --}}
<div class="mb-3">
    <label class="block text-xs font-medium text-gray-600 mb-1">SKU <span class="text-red-500">*</span></label>
    <input type="text" name="sku" value="{{ old('sku', $v->sku ?? '') }}" required
           class="sku-input w-full border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
</div>

{{-- Giá --}}
<div class="grid grid-cols-2 gap-2 mb-3">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Giá bán <span class="text-red-500">*</span></label>
        <input type="number" name="price" value="{{ old('price', $v->price ?? '') }}"
               min="0" step="1000" required
               class="w-full border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Giá gốc (gạch ngang)</label>
        <input type="number" name="compare_price" value="{{ old('compare_price', $v->compare_price ?? '') }}"
               min="0" step="1000"
               class="w-full border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
    </div>
</div>

<div class="mb-3">
    <label class="block text-xs font-medium text-gray-600 mb-1">Giá vốn (nội bộ)</label>
    <input type="number" name="cost_price" value="{{ old('cost_price', $v->cost_price ?? '') }}"
           min="0" step="1000"
           class="w-full border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
</div>

{{-- Thuộc tính --}}
<div class="mb-3">
    <label class="block text-xs font-medium text-gray-600 mb-2">Thuộc tính</label>
    @foreach($attributes as $attribute)
        @php
            // Lấy giá trị đã chọn nếu đang edit
            $selectedValueId = null;
            if ($v) {
                $va = $v->variantAttributes->firstWhere('attribute_id', $attribute->id);
                $selectedValueId = $va?->attribute_value_id;
            }
        @endphp
        <div class="mb-2">
            <label class="block text-xs text-gray-500 mb-1">{{ $attribute->name }}</label>
            <select name="attributes[{{ $attribute->id }}]"
                    class="attr-select w-full border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">— Không chọn —</option>
                @foreach($attribute->values->sortBy('sort_order') as $val)
                    <option value="{{ $val->id }}" @selected(old("attributes.{$attribute->id}", $selectedValueId) == $val->id)>
                        {{ $val->value }}
                        @if($val->color_hex) ({{ $val->color_hex }}) @endif
                    </option>
                @endforeach
            </select>
        </div>
    @endforeach
</div>

{{-- Ảnh (chỉ hiện trong form thêm mới) --}}
@if(!$v)
<div class="mb-3">
    <label class="block text-xs font-medium text-gray-600 mb-1">Ảnh sản phẩm</label>
    <input type="file" name="images[]" multiple accept="image/*"
           class="w-full border rounded-lg px-3 py-1.5 text-sm">
    <input type="hidden" name="primary_image" value="{{ old('primary_image', 0) }}">
    <p class="text-gray-400 text-xs mt-1">Ảnh đầu tiên sẽ được đặt làm ảnh chính. Tối đa 5MB/ảnh.</p>
</div>
@endif

{{-- Trạng thái --}}
<div class="flex items-center gap-2 mb-1">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" id="is_active_{{ $v?->id ?? 'new' }}"
           @checked(old('is_active', $v?->is_active ?? true))
           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
    <label for="is_active_{{ $v?->id ?? 'new' }}" class="text-sm text-gray-700">Hiển thị biến thể này</label>
</div>