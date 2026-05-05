@extends('admin.layouts.admin')

@section('title', 'Quản lý Banner')
@section('page_title', 'Quản lý Banner')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Banner</span>
@endsection

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Danh sách Banner</h1>
            <p class="text-sm text-gray-500 mt-1">Quản lý các banner hiển thị trên trang chủ (Banner chính & Banner phụ)</p>
        </div>
        <a href="{{ route('admin.banners.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl flex items-center gap-2 font-bold shadow-lg shadow-blue-500/30 transition">
            <i class="fas fa-plus"></i> Thêm Banner
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($banners as $banner)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group">
                <div class="relative aspect-[21/9] bg-gray-100">
                    <img src="{{ asset('storage/' . $banner->image_url) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                    <div class="absolute top-3 left-3 flex gap-2">
                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase {{ $banner->type == 'MAIN' ? 'bg-blue-500 text-white' : 'bg-orange-500 text-white' }}">
                            {{ $banner->type == 'MAIN' ? 'Chính' : 'Phụ' }}
                        </span>
                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase {{ $banner->is_active ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' }}">
                            {{ $banner->is_active ? 'Bật' : 'Tắt' }}
                        </span>
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-gray-800 mb-1 truncate">{{ $banner->title ?? 'Không có tiêu đề' }}</h3>
                    <p class="text-xs text-gray-400 mb-4 font-mono truncate">{{ $banner->link_url ?? 'Không có liên kết' }}</p>
                    
                    <div class="flex items-center justify-between border-t border-gray-50 pt-4">
                        <div class="text-xs font-bold text-gray-500">Thứ tự: {{ $banner->sort_order }}</div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.banners.edit', $banner->id) }}"
                               class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100 transition">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa banner này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 bg-red-50 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-100 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                <i class="fas fa-image text-4xl text-gray-200 mb-3"></i>
                <p class="text-gray-400">Chưa có banner nào được tạo.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
