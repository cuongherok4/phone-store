@extends('admin.layouts.admin')

@section('title', isset($brand) ? 'Sửa thương hiệu: ' . $brand->name : 'Thêm thương hiệu mới')
@section('page_title', isset($brand) ? 'Sửa thương hiệu' : 'Thêm thương hiệu mới')

@section('breadcrumb')
    <a href="{{ route('admin.brands.index') }}" class="hover:text-gray-700">Thương hiệu</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">{{ isset($brand) ? 'Sửa' : 'Thêm mới' }}</span>
@endsection

@section('content')

    <div class="max-w-2xl">
        <form action="{{ isset($brand) ? route('admin.brands.update', $brand) : route('admin.brands.store') }}"
              method="POST" enctype="multipart/form-data"
              class="bg-white shadow rounded-lg p-6 space-y-5">
            @csrf
            @if(isset($brand))
                @method('PUT')
            @endif

            {{-- Tên thương hiệu --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Tên thương hiệu <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name"
                       value="{{ old('name', $brand->name ?? '') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                       placeholder="Ví dụ: Apple, Samsung, Xiaomi">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" id="slug"
                       value="{{ old('slug', $brand->slug ?? '') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror"
                       placeholder="Tự động tạo từ tên (để trống)">
                @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Upload logo --}}
            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                <input type="file" name="logo" id="logo" accept="image/*"
                       class="w-full border rounded-lg px-3 py-2 text-sm @error('logo') border-red-500 @enderror"
                       onchange="previewLogo(event)">
                @error('logo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                {{-- Preview ảnh mới --}}
                <img id="logo-preview" src="#" alt="Preview"
                     class="hidden mt-2 w-24 h-24 object-contain rounded-lg border bg-gray-50 p-1">

                {{-- Logo hiện tại (khi edit) --}}
                @if(isset($brand) && $brand->logo)
                    <div class="mt-2 flex items-center gap-4" id="current-logo-wrap">
                        <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}"
                             class="w-24 h-24 object-contain rounded-lg border bg-gray-50 p-1">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" name="delete_logo" value="1" class="rounded">
                            <span class="text-red-500">Xóa logo hiện tại</span>
                        </label>
                    </div>
                @endif
            </div>

            {{-- Trạng thái --}}
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           class="w-4 h-4 text-blue-600 rounded"
                           {{ old('is_active', $brand->is_active ?? true) ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700">Hiển thị thương hiệu này</span>
                </label>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3 pt-2 border-t">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                    <i class="fas fa-save mr-1"></i>
                    {{ isset($brand) ? 'Cập nhật' : 'Thêm mới' }}
                </button>
                <a href="{{ route('admin.brands.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                    Hủy
                </a>
            </div>
        </form>
    </div>

    <script>
        function previewLogo(event) {
            const preview = document.getElementById('logo-preview');
            const file = event.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            }
        }

        document.getElementById('name').addEventListener('input', function () {
            const slugField = document.getElementById('slug');
            if (!slugField.dataset.manual) {
                slugField.value = this.value
                    .toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/đ/g, 'd')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim().replace(/\s+/g, '-');
            }
        });

        document.getElementById('slug').addEventListener('input', function () {
            this.dataset.manual = 'true';
        });
    </script>

@endsection