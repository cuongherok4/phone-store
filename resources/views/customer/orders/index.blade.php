@extends('layouts.app')

@section('title', 'Đơn hàng của tôi - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="tail-container">
        
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Trang chủ</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Đơn hàng của tôi</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <h1 class="text-3xl font-bold text-gray-900 uppercase tracking-tight">Đơn hàng của tôi</h1>
            
            <div class="flex gap-2 overflow-x-auto no-scrollbar pb-2 md:pb-0">
                @php $currentStatus = request('status', 'all'); @endphp
                <a href="{{ route('orders.index') }}" 
                   class="px-4 py-2 rounded-full text-sm font-bold transition {{ $currentStatus === 'all' ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/30' : 'bg-white text-gray-500 hover:bg-gray-100' }}">
                    Tất cả
                </a>
                @foreach(['PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận', 'SHIPPING' => 'Đang giao', 'COMPLETED' => 'Hoàn thành', 'CANCELLED' => 'Đã huỷ'] as $key => $label)
                    <a href="{{ route('orders.index', ['status' => $key]) }}" 
                       class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition {{ $currentStatus === $key ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/30' : 'bg-white text-gray-500 hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            @forelse($orders as $order)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
                    {{-- Header --}}
                    <div class="px-8 py-4 bg-gray-50/50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-bold text-gray-900">Mã đơn: <span class="text-brand-600">#{{ $order->id }}</span></span>
                            <span class="text-gray-300">|</span>
                            <span class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            @php
                                $statusColors = [
                                    'PENDING'   => 'bg-blue-100 text-blue-600',
                                    'CONFIRMED' => 'bg-purple-100 text-purple-600',
                                    'SHIPPING'  => 'bg-orange-100 text-orange-600',
                                    'COMPLETED' => 'bg-green-100 text-green-600',
                                    'CANCELLED' => 'bg-red-100 text-red-600',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-8">
                        <div class="flex flex-col md:flex-row gap-8">
                            {{-- Items (show first item or count) --}}
                            <div class="flex-1 space-y-4">
                                @foreach($order->items as $item)
                                    <div class="flex gap-4">
                                        <div class="w-16 h-16 bg-gray-50 rounded-xl flex-shrink-0 border border-gray-100 p-1 flex items-center justify-center">
                                            @php
                                                $primaryImg = $item->variant->images->where('is_primary', true)->first()?->image_url 
                                                           ?? $item->variant->images->first()?->image_url;
                                                $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/400x400?text=No+Image';
                                            @endphp
                                            <img src="{{ $imgUrl }}" class="w-full h-full object-contain mix-blend-multiply">
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $item->name }}</p>
                                            <p class="text-xs text-gray-500 mt-1">Số lượng: {{ $item->quantity }} | Giá: {{ number_format($item->price, 0, ',', '.') }}đ</p>
                                        </div>
                                    </div>
                                    @if($loop->iteration == 2 && $order->items->count() > 2)
                                        <p class="text-xs text-gray-400 italic">... và {{ $order->items->count() - 2 }} sản phẩm khác</p>
                                        @break
                                    @endif
                                @endforeach
                            </div>

                            {{-- Price & Actions --}}
                            <div class="md:w-48 flex flex-col justify-between items-end gap-4 border-t md:border-t-0 md:border-l border-gray-100 pt-6 md:pt-0 md:pl-8">
                                <div class="text-right">
                                    <p class="text-xs text-gray-400 font-bold uppercase mb-1">Tổng tiền</p>
                                    <p class="text-xl font-black text-red-600">{{ number_format($order->total_price, 0, ',', '.') }}đ</p>
                                </div>
                                <div class="flex flex-col w-full gap-2">
                                    <a href="{{ route('orders.show', $order->id) }}" 
                                       class="block w-full text-center py-2 rounded-xl text-xs font-bold bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 transition">
                                        Chi tiết đơn hàng
                                    </a>
                                    @if($order->canBeCancelled())
                                        <button type="button" 
                                                @click="$dispatch('open-cancel-modal', {id: {{ $order->id }}})"
                                                class="block w-full text-center py-2 rounded-xl text-xs font-bold text-red-500 hover:bg-red-50 transition">
                                            Huỷ đơn
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-16 text-center shadow-sm border border-gray-100">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-box-open text-4xl text-gray-200"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Chưa có đơn hàng nào</h2>
                    <p class="text-gray-500 mb-8">Hãy chọn những sản phẩm ưng ý và đặt hàng ngay bạn nhé!</p>
                    <a href="{{ route('customer.products.index') }}" 
                       class="inline-flex items-center gap-2 bg-brand-600 text-white px-8 py-3 rounded-full font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                        Mua sắm ngay
                    </a>
                </div>
            @endforelse

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    {{-- Cancel Modal (Stub for logic) --}}
    <div x-data="{ open: false, orderId: null }" 
         @open-cancel-modal.window="open = true; orderId = $event.detail.id"
         x-show="open" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl" @click.away="open = false">
            <form :action="'/don-hang/' + orderId + '/huy'" method="POST">
                @csrf
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">Huỷ đơn hàng</h3>
                </div>
                <div class="p-8">
                    <p class="text-sm text-gray-500 mb-6">Bạn có chắc chắn muốn huỷ đơn hàng này? Vui lòng cho chúng tôi biết lý do.</p>
                    <textarea name="reason" required rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 transition outline-none text-sm"
                              placeholder="Nhập lý do huỷ đơn..."></textarea>
                </div>
                <div class="px-8 py-6 bg-gray-50 flex gap-4">
                    <button type="button" @click="open = false" class="flex-1 py-3 rounded-xl font-bold text-gray-500 hover:bg-gray-200 transition">Quay lại</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 transition shadow-lg shadow-red-500/30">Xác nhận huỷ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
