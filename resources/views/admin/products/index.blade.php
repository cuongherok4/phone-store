@extends('admin.layouts.admin')

@section('title', 'Sản Phẩm')
@section('page_title', 'Quản lý Sản phẩm')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Sản phẩm</span>
@endsection

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý Sản phẩm</h1>
        <a href="{{ route('admin.products.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 text-sm">
            + Thêm sản phẩm
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border p-4 mb-6 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Tìm tên sản phẩm..."
               class="border rounded-lg px-3 py-2 text-sm flex-1 min-w-48 focus:ring-2 focus:ring-blue-500 focus:outline-none">

        <select name="brand_id" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Tất cả hãng</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>

        <select name="status" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <option value="">Đang hoạt động</option>
            <option value="0" @selected(request('status') === '0')>Ẩn</option>
            <option value="trashed" @selected(request('status') === 'trashed')>Đã xoá</option>
        </select>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">Lọc</button>
        <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">Xoá lọc</a>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Sản phẩm</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Hãng</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Biến thể</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Trạng thái</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50 {{ $product->deleted_at ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                            <div class="text-gray-400 text-xs mt-0.5">{{ $product->slug }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ optional($product->brand)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded-full font-medium">
                                {{ $product->variants_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($product->deleted_at)
                                <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">Đã xoá</span>
                            @elseif($product->status)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Hiển thị</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.products.variants.index', $product->id) }}"
                                   class="text-indigo-600 hover:underline text-xs">Biến thể</a>
                                @unless($product->deleted_at)
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                       class="text-blue-600 hover:underline text-xs">Sửa</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}"
                                          onsubmit="return confirm('Xoá sản phẩm này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline text-xs">Xoá</button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Không có sản phẩm nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</div>
@endsection