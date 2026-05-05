{{-- Partial dùng chung cho create & edit --}}

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border p-6 space-y-5">

    {{-- Tên sản phẩm --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Tên sản phẩm <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
               required x-on:input="autoSlug($event.target.value)"
               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none @error('name') border-red-400 @enderror">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Slug --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
        <input type="text" name="slug" x-model="slug"
               value="{{ old('slug', $product->slug ?? '') }}"
               class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none @error('slug') border-red-400 @enderror">
        @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Hãng --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Thương hiệu</label>
        <select name="brand_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">— Không chọn —</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id ?? '') == $brand->id)>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Mô tả ngắn --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả ngắn</label>
        <input type="text" name="short_desc" value="{{ old('short_desc', $product->short_desc ?? '') }}"
               maxlength="500"
               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
    </div>

    {{-- Mô tả đầy đủ --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả chi tiết</label>
        <textarea name="description" rows="6"
                  class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    {{-- Thông số kỹ thuật --}}
    <div class="border-t pt-5">
        <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Thông số kỹ thuật</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php $specs = old('specifications', $product->specifications ?? []); @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Màn hình</label>
                <input type="text" name="specifications[screen]" value="{{ $specs['screen'] ?? '' }}"
                       placeholder="Ví dụ: 6.7 inch, Super Retina XDR OLED"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chipset (CPU)</label>
                <input type="text" name="specifications[chipset]" value="{{ $specs['chipset'] ?? '' }}"
                       placeholder="Ví dụ: Apple A16 Bionic"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Camera sau</label>
                <input type="text" name="specifications[camera_rear]" value="{{ $specs['camera_rear'] ?? '' }}"
                       placeholder="Ví dụ: Chính 48 MP & Phụ 12 MP"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Camera trước</label>
                <input type="text" name="specifications[camera_front]" value="{{ $specs['camera_front'] ?? '' }}"
                       placeholder="Ví dụ: 12 MP"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pin & Sạc</label>
                <input type="text" name="specifications[battery_charging]" value="{{ $specs['battery_charging'] ?? '' }}"
                       placeholder="Ví dụ: 4323 mAh, Sạc nhanh 20W"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>
    </div>

    {{-- Trạng thái --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
        <div class="flex gap-4">
            <label class="flex items-center gap-2 text-sm">
                <input type="radio" name="status" value="1"
                       @checked(old('status', $product->status ?? 1) == 1)>
                Hiển thị
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="radio" name="status" value="0"
                       @checked(old('status', $product->status ?? 1) == 0)>
                Ẩn
            </label>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productForm() {
    return {
        slug: '{{ old('slug', $product->slug ?? '') }}',
        autoSlug(value) {
            // Chỉ tự động khi slug chưa bị sửa tay
            this.slug = value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd').replace(/Đ/g, 'd')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        }
    }
}
</script>
@endpush