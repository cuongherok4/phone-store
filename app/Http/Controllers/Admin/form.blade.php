@extends('admin.layouts.admin')

@php
    $isEdit = isset($supplier);
    $title = $isEdit ? 'Sửa Nhà Cung Cấp' : 'Thêm Nhà Cung Cấp';
@endphp

@section('title', $title)
@section('page_title', $title)

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.suppliers.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $title }}</h1>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.suppliers.update', $supplier->id) : route('admin.suppliers.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="bg-white p-8 rounded-lg shadow-md space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Tên nhà cung cấp <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $supplier->name ?? '') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="contact_person" class="block text-sm font-medium text-gray-700">Người liên hệ</label>
                    <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $supplier->email ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $supplier->phone ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                <textarea name="address" id="address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $supplier->address ?? '') }}</textarea>
            </div>

            <div class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true)) class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">Kích hoạt</label>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                {{ $isEdit ? 'Cập nhật' : 'Lưu' }}
            </button>
            <a href="{{ route('admin.suppliers.index') }}" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-2 rounded-lg">Huỷ</a>
        </div>
    </form>
</div>
@endsection