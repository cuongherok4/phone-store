@extends('admin.layouts.admin')
@section('title', 'Sửa Sản Phẩm')
@section('page_title', 'Sửa Sản Phẩm')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Sản phẩm</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Sửa</span>
@endsection

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.products.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Sửa Sản Phẩm</h1>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" x-data="productForm()">
        @csrf @method('PUT')
        @include('admin.products._form')

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                Cập nhật
            </button>
            <a href="{{ route('admin.products.index') }}"
               class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-2 rounded-lg">Huỷ</a>

            {{-- Nút đến trang quản lý biến thể --}}
            <a href="{{ route('admin.products.variants.index', $product->id) }}"
               class="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">
                Quản lý biến thể →
            </a>
        </div>
    </form>
</div>
@endsection