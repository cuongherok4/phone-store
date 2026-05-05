@extends('layouts.app')

@section('title', 'Đặt hàng thành công - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-16">
    <div class="tail-container">
        
        <div class="max-w-3xl mx-auto">
            {{-- Success Card --}}
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 text-center mb-8">
                <div class="bg-brand-600 h-3"></div>
                <div class="p-12">
                    <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-8 shadow-lg shadow-green-100/50">
                        <i class="fas fa-check text-4xl"></i>
                    </div>
                    
                    <h1 class="text-3xl font-black text-gray-900 mb-4 uppercase tracking-tight">Đặt hàng thành công!</h1>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">
                        Cảm ơn bạn đã tin tưởng mua sắm tại <span class="text-brand-600 font-bold">Phone Store</span>. 
                        Đơn hàng của bạn đang được hệ thống xử lý.
                    </p>

                    <div class="inline-flex flex-col md:flex-row items-center gap-4 bg-gray-50 rounded-2xl p-6 border border-gray-100 w-full mb-10">
                        <div class="flex-1">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">Mã đơn hàng</p>
                            <p class="text-xl font-black text-brand-600">#{{ $order->id }}</p>
                        </div>
                        <div class="w-px h-10 bg-gray-200 hidden md:block"></div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">Trạng thái</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-600 uppercase">
                                {{ $order->status_label }}
                            </span>
                        </div>
                        <div class="w-px h-10 bg-gray-200 hidden md:block"></div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">Tổng tiền</p>
                            <p class="text-xl font-black text-red-600">{{ number_format($order->total_price, 0, ',', '.') }}đ</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('home') }}" 
                           class="flex items-center justify-center gap-2 bg-gray-900 text-white px-8 py-4 rounded-xl font-bold hover:bg-gray-800 transition shadow-lg">
                            <i class="fas fa-home"></i> Về trang chủ
                        </a>
                        <a href="{{ route('customer.products.index') }}" 
                           class="flex items-center justify-center gap-2 bg-white text-gray-900 border-2 border-gray-100 px-8 py-4 rounded-xl font-bold hover:bg-gray-50 transition">
                            <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>

            {{-- Summary Details --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="fas fa-info-circle text-brand-600"></i>
                    Chi tiết đơn hàng
                </h3>
                
                <div class="space-y-6">
                    {{-- Customer Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                        <div>
                            <p class="text-xs text-gray-400 font-bold uppercase mb-2">Thông tin người nhận</p>
                            <p class="text-sm font-bold text-gray-900 mb-1">{{ $order->shipping_name }}</p>
                            <p class="text-sm text-gray-600">{{ $order->shipping_phone }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-bold uppercase mb-2">Địa chỉ giao hàng</p>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $order->shipping_address }}</p>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="space-y-4">
                        <p class="text-xs text-gray-400 font-bold uppercase">Sản phẩm đã đặt</p>
                        @foreach($order->items as $item)
                            <div class="flex items-center gap-4 py-3 border-b border-gray-50 last:border-0">
                                <div class="w-14 h-14 bg-gray-50 rounded-lg flex-shrink-0 border border-gray-100 p-1 flex items-center justify-center">
                                    @php
                                        $primaryImg = $item->variant->images->where('is_primary', true)->first()?->image_url 
                                                   ?? $item->variant->images->first()?->image_url;
                                        $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/400x400?text=No+Image';
                                    @endphp
                                    <img src="{{ $imgUrl }}" class="w-full h-full object-contain mix-blend-multiply">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Số lượng: {{ $item->quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
