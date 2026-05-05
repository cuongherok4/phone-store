@extends('admin.layouts.admin')

@section('title', 'Chỉnh sửa Banner')
@section('page_title', 'Chỉnh sửa Banner')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.banners.index') }}" class="hover:text-blue-600">Banner</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Chỉnh sửa</span>
@endsection

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 space-y-6">
                
                {{-- Title --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tiêu đề Banner</label>
                    <input type="text" name="title" value="{{ old('title', $banner->title) }}"
                           placeholder="VD: iPhone 15 Pro Max - Siêu phẩm camera"
                           class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Image --}}
                <div x-data="{ photoName: null, photoPreview: '{{ asset('storage/' . $banner->image_url) }}' }">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Hình ảnh Banner <span class="text-red-500">*</span></label>
                    <div class="mt-2 flex items-center gap-6">
                        <div class="relative w-full aspect-[21/9] rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden flex items-center justify-center group">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" class="w-full h-full object-cover">
                            </template>
                            <input type="file" name="image" class="absolute inset-0 opacity-0 cursor-pointer"
                                   x-ref="photo"
                                   @change="
                                        photoName = $refs.photo.files[0].name;
                                        const reader = new FileReader();
                                        reader.onload = (e) => { photoPreview = e.target.result; };
                                        reader.readAsDataURL($refs.photo.files[0]);
                                   ">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white pointer-events-none">
                                <span class="text-xs font-bold">Click để thay đổi ảnh</span>
                            </div>
                        </div>
                    </div>
                    @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Type --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Loại Banner</label>
                        <select name="type" class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                            <option value="MAIN" {{ $banner->type == 'MAIN' ? 'selected' : '' }}>Banner chính (Lớn - Slider)</option>
                            <option value="SECONDARY" {{ $banner->type == 'SECONDARY' ? 'selected' : '' }}>Banner phụ (Nhỏ - Cạnh slider)</option>
                        </select>
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order) }}"
                               class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>

                {{-- Link --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Liên kết khi click (URL)</label>
                    <input type="text" name="link_url" value="{{ old('link_url', $banner->link_url) }}"
                           placeholder="VD: /san-pham/iphone-15-pro-max"
                           class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                {{-- Status --}}
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }}
                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-bold text-gray-700">Kích hoạt banner này</label>
                </div>

            </div>
            <div class="bg-gray-50 px-8 py-4 flex items-center justify-end gap-4 border-t">
                <a href="{{ route('admin.banners.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition">Hủy</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-500/30 transition">
                    Cập nhật Banner
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
