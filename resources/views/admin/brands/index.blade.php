@extends('admin.layouts.admin')

@section('title', 'Quản lý Thương hiệu')
@section('page_title', 'Quản lý Thương hiệu')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Thương hiệu</span>
@endsection

@section('content')

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded flex items-center justify-between">
            <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex items-center justify-between">
            <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <p class="text-gray-500 text-sm">Tổng: <strong>{{ $brands->total() }}</strong> thương hiệu</p>
        <a href="{{ route('admin.brands.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> Thêm thương hiệu
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">Logo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên thương hiệu</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Trạng thái</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($brands as $brand)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $brand->id }}</td>
                        <td class="px-6 py-4">
                            @if($brand->logo)
                                <img src="{{ Storage::url($brand->logo) }}" alt="{{ $brand->name }}"
                                     class="w-12 h-12 object-contain rounded border bg-gray-50 p-1">
                            @else
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-xs"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-800">{{ $brand->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $brand->slug }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($brand->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Hiện
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Ẩn
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.brands.edit', $brand) }}"
                                   class="text-indigo-600 hover:text-indigo-900 p-1" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST"
                                      onsubmit="return confirm('Xóa thương hiệu \"{{ $brand->name }}\"?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-building text-4xl mb-3 block"></i>
                            Chưa có thương hiệu nào.
                            <a href="{{ route('admin.brands.create') }}" class="text-blue-500 hover:underline ml-1">Tạo ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($brands->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $brands->links() }}
            </div>
        @endif
    </div>

@endsection