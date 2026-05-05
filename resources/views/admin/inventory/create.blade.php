@extends('admin.layouts.admin')
@section('title', 'Nhập hàng vào kho')
@section('page_title', 'Nhập hàng vào kho')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.inventory.index') }}" class="text-gray-500 hover:text-gray-700">Tồn kho</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Nhập hàng</span>
@endsection

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.inventory.store') }}" method="POST" class="bg-white shadow rounded-lg p-6 space-y-5">
            @csrf

            {{-- Nhà cung cấp --}}
            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Nhà cung cấp <span class="text-red-500">*</span>
                </label>
                <select name="supplier_id" id="supplier_id" 
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Chọn nhà cung cấp --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sản phẩm --}}
            <div>
                <label for="variant_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Sản phẩm (Phiên bản) <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4 items-start border rounded-lg p-3 bg-gray-50">
                    <div id="variant-image-preview" class="w-16 h-16 bg-white rounded border flex-shrink-0 flex items-center justify-center p-1 overflow-hidden">
                        <i class="fas fa-image text-gray-300 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <select name="variant_id" id="variant_id" 
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" data-image="">-- Chọn sản phẩm để nhập --</option>
                            @foreach($variants as $variant)
                                @php
                                    $primaryImg = $variant->images->where('is_primary', true)->first()?->image_url 
                                               ?? $variant->images->first()?->image_url;
                                    $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : '';
                                @endphp
                                <option value="{{ $variant->id }}" 
                                        data-image="{{ $imgUrl }}"
                                        {{ (old('variant_id') == $variant->id || request('variant_id') == $variant->id) ? 'selected' : '' }}>
                                    {{ $variant->product->name ?? 'N/A' }} 
                                    @if($variant->variantAttributes->count() > 0)
                                        - {{ $variant->variantAttributes->pluck('attributeValue.value')->implode(', ') }}
                                    @endif
                                    (SKU: {{ $variant->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('variant_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Số lượng --}}
                <div class="space-y-2">
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Số lượng <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1" 
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('quantity')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Giá nhập --}}
                <div class="space-y-2">
                    <label for="import_price" class="block text-sm font-medium text-gray-700">Giá nhập (VNĐ)</label>
                    <div class="relative">
                        <input type="number" name="import_price" id="import_price" value="{{ old('import_price') }}" min="0" 
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pl-8" placeholder="0">
                        <span class="absolute left-3 top-2 text-gray-400 text-sm">₫</span>
                    </div>
                    <p class="text-[10px] text-gray-400 italic mt-1">Để trống nếu không cần theo dõi giá nhập.</p>
                    @error('import_price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Ghi chú --}}
            <div>
                <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                <textarea name="note" id="note" rows="2" 
                          class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
                          placeholder="VD: Nhập lô hàng tháng 10...">{{ old('note') }}</textarea>
                @error('note')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3 pt-2 border-t">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-1"></i> Xác nhận nhập hàng
                </button>
                <a href="{{ route('admin.inventory.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Hủy
                </a>
            </div>
        </form>
    </div>


@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const variantSelect = document.getElementById('variant_id');
        const previewContainer = document.getElementById('variant-image-preview');

        function updatePreview() {
            const selectedOption = variantSelect.options[variantSelect.selectedIndex];
            const imageUrl = selectedOption.getAttribute('data-image');

            if (imageUrl) {
                previewContainer.innerHTML = `<img src="${imageUrl}" class="max-w-full max-h-full object-contain mix-blend-multiply">`;
            } else {
                previewContainer.innerHTML = `<i class="fas fa-image text-gray-300 text-xl"></i>`;
            }
        }

        variantSelect.addEventListener('change', updatePreview);
        
        // Khởi tạo preview nếu đã có giá trị sẵn (old hoặc request)
        updatePreview();
    });
</script>
@endpush
