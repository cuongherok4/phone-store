@extends('admin.layouts.admin')

@section('title', 'Cấu hình hệ thống')
@section('page_title', 'Cấu hình hệ thống')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Cấu hình</span>
@endsection

@section('content')
<div class="p-6">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-6 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        <div class="space-y-8">
            @foreach($settings as $group => $items)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas {{ $group == 'vnpay' ? 'fa-credit-card' : 'fa-cog' }}"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800 uppercase tracking-wider">
                            {{ $group == 'vnpay' ? 'Cấu hình VNPAY' : ($group == 'store' ? 'Thông tin cửa hàng' : 'Cấu hình chung') }}
                        </h2>
                        @if($group == 'vnpay')
                            <a href="https://sandbox.vnpayment.vn/merchantv2/Users/Login.htm" target="_blank"
                               class="ml-auto text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 transition">
                                <i class="fas fa-external-link-alt"></i> Quản trị VNPAY
                            </a>
                        @endif
                    </div>
                    <div class="p-6 space-y-6">
                        @foreach($items as $setting)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">
                                        {{ $setting->description }}
                                    </label>
                                    <code class="text-[10px] text-gray-400 font-mono">{{ $setting->key }}</code>
                                </div>
                                <div class="md:col-span-2">
                                    @if(Str::contains($setting->key, 'secret') || Str::contains($setting->key, 'key'))
                                        <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                               class="w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                                    @else
                                        <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                               class="w-full border rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-end gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-500/30 transition">
                    Lưu tất cả thay đổi
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
