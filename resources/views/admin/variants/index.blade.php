@extends('admin.layouts.admin')
@section('title', 'Biến thể — ' . $product->name)
@section('page_title', 'Quản lý Biến thể')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Sản phẩm</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Biến thể</span>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productName = "{{ $product->name }}";
    
    // Hàm tạo slug/chuẩn hóa text
    function sanitize(text) {
        return text.normalize("NFD")
                   .replace(/[\u0300-\u036f]/g, "")
                   .replace(/đ/g, "d").replace(/Đ/g, "D")
                   .replace(/[^a-zA-Z0-9]/g, "")
                   .toUpperCase();
    }

    function updateSKU(container) {
        const skuInput = container.querySelector('.sku-input');
        const selects = container.querySelectorAll('.attr-select');
        
        let skuParts = [sanitize(productName)];
        
        selects.forEach(select => {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value !== "") {
                // Lấy text của option (ví dụ "Đen", "8GB"...)
                let attrText = selectedOption.text.split('(')[0].trim();
                skuParts.push(sanitize(attrText));
            }
        });
        
        skuInput.value = skuParts.join('-');
    }

    // Lắng nghe sự kiện change trên tất cả các select thuộc tính
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('attr-select')) {
            // Tìm form container gần nhất
            const container = e.target.closest('form');
            if (container) {
                updateSKU(container);
            }
        }
    });
});
</script>
@endpush

@section('content')
<div class="p-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.products.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Biến thể: {{ $product->name }}</h1>
            <p class="text-sm text-gray-400">{{ $product->variants->count() }} biến thể</p>
        </div>
        <a href="{{ route('admin.products.edit', $product->id) }}"
           class="ml-auto text-sm text-blue-600 hover:underline">← Sửa sản phẩm</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        {{-- ===== DANH SÁCH VARIANTS ===== --}}
        <div class="xl:col-span-3 space-y-4">
            <h2 class="font-semibold text-gray-700">Danh sách biến thể</h2>

            @forelse($product->variants as $variant)
                @php
                    $attrs = $variant->variantAttributes
                        ->map(fn($va) => $va->attributeValue->attribute->name . ': ' . $va->attributeValue->value)
                        ->join(' | ');
                    $primaryImg = $variant->images->firstWhere('is_primary', true) ?? $variant->images->first();
                @endphp

                <div class="bg-white rounded-xl border shadow-sm overflow-hidden" x-data="{ editOpen: false }">
                    {{-- Header variant --}}
                    <div class="flex items-center gap-4 p-4">
                        {{-- Ảnh --}}
                        <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            @if($primaryImg)
                                <img src="{{ Storage::url($primaryImg->image_url) }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">No img</div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900">{{ $variant->sku }}</div>
                            <div class="text-sm text-gray-500">{{ $attrs ?: 'Không có thuộc tính' }}</div>
                            <div class="text-sm font-medium text-blue-600 mt-0.5">
                                {{ number_format($variant->price, 0, '.', '.') }}đ
                                @if($variant->compare_price)
                                    <span class="line-through text-gray-400 ml-1 text-xs">{{ number_format($variant->compare_price, 0, '.', '.') }}đ</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $variant->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $variant->is_active ? 'Hiện' : 'Ẩn' }}
                            </span>
                            <button x-on:click="editOpen = !editOpen"
                                    class="text-sm text-blue-600 hover:underline">Sửa</button>
                            <form method="POST" action="{{ route('admin.variants.destroy', $variant->id) }}"
                                  onsubmit="return confirm('Xoá biến thể {{ $variant->sku }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:underline">Xoá</button>
                            </form>
                        </div>
                    </div>

                    {{-- Form sửa variant (ẩn/hiện) --}}
                    <div x-show="editOpen" x-cloak class="border-t px-4 py-4 bg-gray-50">
                        <form method="POST" action="{{ route('admin.variants.update', $variant->id) }}"
                              enctype="multipart/form-data">
                            @csrf @method('PUT')
                            @include('admin.variants._variant_fields', ['v' => $variant])

                            <div class="mt-4 flex gap-2">
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium">
                                    Lưu thay đổi
                                </button>
                                <button type="button" x-on:click="editOpen = false"
                                        class="border border-gray-300 hover:bg-gray-100 text-gray-600 px-4 py-1.5 rounded-lg text-sm">
                                    Huỷ
                                </button>
                            </div>
                        </form>

                        {{-- Ảnh của variant --}}
                        <div class="mt-4 border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Ảnh biến thể</p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($variant->images->sortBy('sort_order') as $img)
                                    <div class="relative group w-16 h-16">
                                        <img src="{{ Storage::url($img->image_url) }}"
                                             class="w-full h-full object-cover rounded-lg border-2 {{ $img->is_primary ? 'border-blue-500' : 'border-transparent' }}">
                                        {{-- Set primary --}}
                                        @unless($img->is_primary)
                                            <form method="POST" action="{{ route('admin.variants.set-primary', $img->id) }}">
                                                @csrf
                                                <button type="submit" title="Đặt làm ảnh chính"
                                                        class="absolute top-0 left-0 hidden group-hover:flex items-center justify-center w-full h-full bg-black/40 rounded-lg text-white text-xs">
                                                    ★
                                                </button>
                                            </form>
                                        @else
                                            <span class="absolute top-0 right-0 bg-blue-500 text-white text-[10px] rounded-bl-lg px-1">Chính</span>
                                        @endunless
                                        {{-- Xoá ảnh --}}
                                        <form method="POST" action="{{ route('admin.variants.delete-image', $img->id) }}"
                                              onsubmit="return confirm('Xoá ảnh này?')"
                                              class="absolute -top-1.5 -right-1.5">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-xs leading-none">×</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                            {{-- Upload ảnh mới --}}
                            <form method="POST" action="{{ route('admin.variants.upload-images', $variant->id) }}"
                                  enctype="multipart/form-data" class="flex items-center gap-2">
                                @csrf
                                <input type="file" name="images[]" multiple accept="image/*"
                                       class="text-xs border rounded-lg px-2 py-1 flex-1">
                                <button type="submit"
                                        class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1.5 rounded-lg text-xs">
                                    Upload
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border p-8 text-center text-gray-400">
                    Chưa có biến thể nào. Thêm biến thể đầu tiên ở bên phải.
                </div>
            @endforelse
        </div>

        {{-- ===== FORM THÊM VARIANT MỚI ===== --}}
        <div class="xl:col-span-2">
            <div class="bg-white rounded-xl border shadow-sm p-5 sticky top-6">
                <h2 class="font-semibold text-gray-700 mb-4">Thêm biến thể mới</h2>
                <form method="POST" action="{{ route('admin.products.variants.store', $product->id) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @include('admin.variants._variant_fields', ['v' => null])

                    <button type="submit"
                            class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium text-sm">
                        + Thêm biến thể
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection