@extends('admin.layouts.admin')

@php
    $isEdit = isset($supplier) && $supplier->exists;
    $title = $isEdit ? 'Chỉnh sửa Nhà cung cấp' : 'Thêm Nhà cung cấp mới';
@endphp

@section('title', $title)
@section('page_title', $title)

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Trang chủ</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.suppliers.index') }}">Nhà cung cấp</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">{{ $isEdit ? 'Sửa' : 'Thêm' }}</span>
@endsection

@section('content')

    <div class="max-w-4xl">
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <form action="{{ $isEdit ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}" 
                  method="POST">
                @csrf
                @if($isEdit) @method('PUT') @endif

                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Tên nhà cung cấp --}}
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Tên nhà cung cấp <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" 
                                   value="{{ old('name', $supplier->name ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-lg border @error('name') border-red-500 @else border-gray-300 @enderror focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="VD: Apple Inc., Samsung Vina..." required>
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Người liên hệ --}}
                        <div>
                            <label for="contact_person" class="block text-sm font-semibold text-gray-700 mb-2">Người liên hệ</label>
                            <input type="text" name="contact_person" id="contact_person" 
                                   value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="VD: Nguyễn Văn A">
                        </div>

                        {{-- Số điện thoại --}}
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Số điện thoại</label>
                            <input type="text" name="phone" id="phone" 
                                   value="{{ old('phone', $supplier->phone ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="VD: 0912345678">
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', $supplier->email ?? '') }}"
                                   class="w-full px-4 py-2.5 rounded-lg border @error('email') border-red-500 @else border-gray-300 @enderror focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="VD: contact@apple.com">
                            @error('email')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Trạng thái --}}
                        <div class="flex items-center mt-8">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">Đang hoạt động</span>
                            </label>
                        </div>

                        {{-- Địa chỉ --}}
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">Địa chỉ</label>
                            <textarea name="address" id="address" rows="3"
                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Địa chỉ chi tiết của nhà cung cấp...">{{ old('address', $supplier->address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-4 bg-gray-50 border-t flex justify-end gap-3">
                    <a href="{{ route('admin.suppliers.index') }}" 
                       class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition">
                        Hủy
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fas fa-save"></i> {{ $isEdit ? 'Cập nhật' : 'Lưu lại' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
