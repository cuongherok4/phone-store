@extends('admin.layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->id)
@section('page_title', 'Chi tiết đơn hàng')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('admin.orders.index') }}" class="hover:text-blue-600">Đơn hàng</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">#{{ $order->id }}</span>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-8">
        
        {{-- Order Items --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-50">
                <h3 class="text-lg font-bold text-gray-900 uppercase tracking-tight">Sản phẩm trong đơn</h3>
            </div>
            <div class="p-8">
                <div class="space-y-6">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-20 bg-gray-50 rounded-2xl flex-shrink-0 border border-gray-100 p-2 flex items-center justify-center">
                                @php
                                    $primaryImg = $item->variant->images->where('is_primary', true)->first()?->image_url 
                                               ?? $item->variant->images->first()?->image_url;
                                    $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/400x400?text=No+Image';
                                @endphp
                                <img src="{{ $imgUrl }}" class="w-full h-full object-contain mix-blend-multiply">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 truncate">{{ $item->name }}</h4>
                                <p class="text-xs text-gray-500 mb-1">SKU: {{ $item->sku }}</p>
                                <p class="text-sm font-bold text-blue-600">{{ number_format($item->price, 0, ',', '.') }}đ <span class="text-gray-400 font-medium ml-2">x {{ $item->quantity }}</span></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">{{ number_format($item->subtotal, 0, ',', '.') }}đ</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 pt-8 border-t border-gray-100 space-y-3">
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Tạm tính ({{ $order->items->count() }} sản phẩm)</span>
                        <span>{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-sm text-red-500">
                            <span>Giảm giá (Coupon)</span>
                            <span>-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Phí vận chuyển</span>
                        <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="flex justify-between text-xl font-bold text-gray-900 pt-3">
                        <span>Tổng cộng</span>
                        <span class="text-blue-600">{{ number_format($order->total_price, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Timeline --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-tight mb-8">Lịch sử đơn hàng</h3>
            <div class="space-y-8 relative before:absolute before:inset-0 before:left-4 before:w-0.5 before:bg-gray-100 before:h-full">
                @foreach($order->statusHistories as $history)
                    <div class="relative pl-12">
                        <div class="absolute left-0 top-1 w-8 h-8 rounded-full bg-white border-4 border-blue-500 flex items-center justify-center z-10"></div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold uppercase text-blue-600">{{ \App\Models\Order::getLabel($history->new_status) }}</span>
                                <span class="text-[10px] text-gray-400 font-medium">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $history->note }}</p>
                            <p class="text-[10px] text-gray-400 mt-2 italic">Thực hiện bởi: {{ $history->changedBy?->name ?? 'Hệ thống' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Sidebar Details --}}
    <div class="space-y-8">
        
        {{-- Status Action --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-6">Trạng thái đơn hàng</h3>
            
            <div class="mb-6">
                @php
                    $statusClasses = [
                        'PENDING' => 'bg-amber-50 text-amber-600 border-amber-200',
                        'CONFIRMED' => 'bg-blue-50 text-blue-600 border-blue-200',
                        'SHIPPING' => 'bg-indigo-50 text-indigo-600 border-indigo-200',
                        'COMPLETED' => 'bg-green-50 text-green-600 border-green-200',
                        'CANCELLED' => 'bg-red-50 text-red-600 border-red-200',
                    ];
                @endphp
                <div class="inline-flex px-4 py-2 rounded-xl border font-bold text-sm uppercase {{ $statusClasses[$order->status] ?? 'bg-gray-50 border-gray-200' }}">
                    {{ $order->status_label }}
                </div>
            </div>

            <div class="mb-8">
                <a href="{{ route('admin.orders.invoice', $order->id) }}" 
                   class="w-full inline-flex items-center justify-center gap-2 bg-gray-50 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-100 transition border border-gray-200">
                    <i class="fas fa-file-pdf"></i> In hoá đơn PDF
                </a>
            </div>

            <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST" class="space-y-4 pt-8 border-t border-gray-100">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 ml-1">Thay đổi trạng thái sang</label>
                    <select name="status" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all">
                        @foreach(['PENDING', 'CONFIRMED', 'SHIPPING', 'COMPLETED', 'CANCELLED'] as $st)
                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>
                                {{ \App\Models\Order::getLabel($st) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <textarea name="note" rows="3" placeholder="Ghi chú lý do cập nhật..." 
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all"></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/25 active:scale-[0.98]">
                    Lưu cập nhật
                </button>
            </form>
        </div>

        {{-- Customer Info --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-6">Thông tin khách hàng</h3>
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-lg">
                    {{ strtoupper(substr($order->user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-900 leading-tight">{{ $order->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $order->user->email }}</p>
                </div>
            </div>
            <div class="space-y-4 pt-6 border-t border-gray-50">
                <div class="flex items-start gap-3">
                    <i class="fas fa-phone text-gray-400 text-xs mt-1"></i>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Số điện thoại</p>
                        <p class="text-sm text-gray-700">{{ $order->shipping_phone }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fas fa-map-marker-alt text-gray-400 text-xs mt-1"></i>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Địa chỉ giao hàng</p>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            <strong>{{ $order->shipping_name }}</strong><br>
                            {{ $order->shipping_address }}
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fas fa-credit-card text-gray-400 text-xs mt-1"></i>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Phương thức thanh toán</p>
                        <p class="text-sm text-gray-700 uppercase">{{ $order->payment_method }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
