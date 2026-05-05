@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="tail-container">
        
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('orders.index') }}" class="hover:text-brand-600 transition">Đơn hàng của tôi</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Chi tiết #{{ $order->id }}</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 uppercase tracking-tight mb-2">Chi tiết đơn hàng #{{ $order->id }}</h1>
                <p class="text-sm text-gray-500">Ngày đặt hàng: <span class="font-bold text-gray-700">{{ $order->created_at->format('d/m/Y H:i') }}</span></p>
            </div>
            
            <div class="flex items-center gap-4">
                @if($order->canBeCancelled())
                    <button type="button" 
                            onclick="document.getElementById('cancel-modal').style.display='flex'"
                            class="px-6 py-3 rounded-xl text-sm font-bold text-red-500 bg-red-50 hover:bg-red-100 transition">
                        Huỷ đơn hàng
                    </button>
                @endif
                <a href="{{ route('customer.products.index') }}" 
                   class="px-6 py-3 rounded-xl text-sm font-bold bg-brand-600 text-white hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                    Tiếp tục mua sắm
                </a>
            </div>
        </div>

        {{-- Order Status Stepper --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mb-8">
            @php
                $steps = [
                    'PENDING'   => ['icon' => 'fa-clock', 'label' => 'Chờ xác nhận'],
                    'CONFIRMED' => ['icon' => 'fa-check-circle', 'label' => 'Đã xác nhận'],
                    'SHIPPING'  => ['icon' => 'fa-shipping-fast', 'label' => 'Đang giao'],
                    'COMPLETED' => ['icon' => 'fa-hand-holding-heart', 'label' => 'Hoàn thành']
                ];
                $allStatuses = array_keys($steps);
                $currentIndex = array_search($order->status, $allStatuses);
                if ($order->status === 'CANCELLED') {
                    $steps['CANCELLED'] = ['icon' => 'fa-times-circle', 'label' => 'Đã huỷ'];
                    $currentIndex = count($steps) - 1;
                }
            @endphp
            
            <div class="relative flex justify-between items-start">
                {{-- Progress Line --}}
                <div class="absolute top-6 left-0 w-full h-1 bg-gray-100 -z-0">
                    <div class="h-full bg-brand-500 transition-all duration-1000" 
                         style="width: {{ $order->status === 'CANCELLED' ? '0%' : ($currentIndex !== false ? ($currentIndex / (count($steps) - 1) * 100) : '0') }}%"></div>
                </div>

                @foreach($steps as $status => $step)
                    @php
                        $isActive = $currentIndex !== false && array_search($status, $allStatuses) <= $currentIndex;
                        if ($order->status === 'CANCELLED' && $status !== 'CANCELLED') $isActive = false;
                        if ($order->status === 'CANCELLED' && $status === 'CANCELLED') $isActive = true;
                    @endphp
                    <div class="flex flex-col items-center relative z-10">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500 {{ $isActive ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/40 ring-4 ring-brand-50' : 'bg-white text-gray-300 border-2 border-gray-100' }}">
                            <i class="fas {{ $step['icon'] }} text-lg"></i>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase tracking-tighter {{ $isActive ? 'text-brand-600' : 'text-gray-400' }}">{{ $step['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT: Info & Items --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Shipping & Payment Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Shipping --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 hover:shadow-md transition duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Giao tới</h3>
                        </div>
                        <div class="space-y-2">
                            <p class="text-base font-bold text-gray-900">{{ $order->shipping_name }}</p>
                            <p class="text-sm text-gray-500 font-medium">{{ $order->shipping_phone }}</p>
                            <div class="mt-4 pt-4 border-t border-gray-50">
                                <p class="text-sm text-gray-600 leading-relaxed italic">{{ $order->shipping_address }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 hover:shadow-md transition duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Thanh toán</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Phương thức</p>
                                <p class="text-sm font-bold text-gray-900">{{ $order->payment_method === 'MOMO' ? 'Ví MoMo' : 'Thanh toán khi nhận hàng (COD)' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Trạng thái</p>
                                @php
                                    $pStatus = [
                                        'UNPAID' => ['label' => 'Chưa thanh toán', 'class' => 'text-orange-600 bg-orange-50'],
                                        'PAID'   => ['label' => 'Đã thanh toán', 'class' => 'text-green-600 bg-green-50'],
                                        'REFUNDED' => ['label' => 'Đã hoàn tiền', 'class' => 'text-gray-600 bg-gray-50'],
                                    ];
                                    $currP = $pStatus[$order->payment_status] ?? ['label' => $order->payment_status, 'class' => 'text-gray-600 bg-gray-50'];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $currP['class'] }}">
                                    {{ $currP['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Items --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ 
                    reviewModal: false, 
                    reviewItem: {id: null, name: '', img: ''},
                    rating: 5,
                    openReview(id, name, img) {
                        this.reviewItem = {id, name, img};
                        this.rating = 5;
                        this.reviewModal = true;
                    }
                }">
                    <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 uppercase tracking-tight">Danh sách sản phẩm</h3>
                        <span class="text-xs font-medium text-gray-400">{{ $order->items->count() }} sản phẩm</span>
                    </div>
                    <div class="p-8">
                        <div class="divide-y divide-gray-50">
                            @foreach($order->items as $item)
                                <div class="py-6 first:pt-0 last:pb-0 flex items-center gap-6 group">
                                    <div class="w-24 h-24 bg-gray-50 rounded-2xl flex-shrink-0 border border-gray-100 p-3 flex items-center justify-center group-hover:bg-white group-hover:shadow-xl transition-all duration-300">
                                        @php
                                            $primaryImg = $item->variant->images->where('is_primary', true)->first()?->image_url 
                                                       ?? $item->variant->images->first()?->image_url;
                                            $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/400x400?text=No+Image';
                                        @endphp
                                        <img src="{{ $imgUrl }}" class="w-full h-full object-contain mix-blend-multiply">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('customer.products.show', $item->variant->product->slug ?? '#') }}" 
                                           class="text-lg font-bold text-gray-900 hover:text-brand-600 transition truncate block mb-1">
                                            {{ $item->name }}
                                        </a>
                                        <p class="text-sm text-gray-500 mb-3">Số lượng: <span class="font-bold text-gray-900">{{ $item->quantity }}</span></p>
                                        
                                        @if($order->status === 'COMPLETED')
                                            @if(!$item->review)
                                                <button @click="openReview({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $imgUrl }}')" 
                                                        class="inline-flex items-center gap-2 text-xs font-bold text-brand-600 hover:gap-3 transition-all bg-brand-50 px-4 py-2 rounded-lg hover:bg-brand-100">
                                                    <i class="fas fa-star text-[10px]"></i> Đánh giá ngay
                                                </button>
                                            @else
                                                <span class="inline-flex items-center gap-2 text-xs font-bold text-green-600 bg-green-50 px-4 py-2 rounded-lg">
                                                    <i class="fas fa-check-circle text-[10px]"></i> Đã đánh giá
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-black text-gray-900">{{ number_format($item->subtotal, 0, ',', '.') }}đ</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ number_format($item->price, 0, ',', '.') }}đ/cái</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Review Modal --}}
                    <div x-show="reviewModal" 
                         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         style="display: none;">
                        <div class="bg-white rounded-3xl w-full max-w-xl overflow-hidden shadow-2xl" @click.away="reviewModal = false">
                            <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="order_item_id" :value="reviewItem.id">
                                
                                <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                                    <h3 class="text-xl font-bold text-gray-900">Đánh giá sản phẩm</h3>
                                    <button type="button" @click="reviewModal = false" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <div class="p-8 space-y-8">
                                    {{-- Product Brief --}}
                                    <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                        <img :src="reviewItem.img" class="w-16 h-16 object-contain mix-blend-multiply">
                                        <p class="text-sm font-bold text-gray-900" x-text="reviewItem.name"></p>
                                    </div>

                                    {{-- Star Rating --}}
                                    <div class="flex flex-col items-center gap-4">
                                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Chất lượng sản phẩm</p>
                                        <div class="flex gap-2">
                                            @foreach([1,2,3,4,5] as $val)
                                                <button type="button" 
                                                        @click="rating = {{ $val }}"
                                                        class="w-12 h-12 rounded-xl transition-all duration-300 flex items-center justify-center text-2xl"
                                                        :class="rating >= {{ $val }} ? 'bg-yellow-100 text-yellow-500 scale-110 shadow-lg shadow-yellow-500/20' : 'bg-gray-50 text-gray-200'">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="rating" :value="rating">
                                        <p class="text-sm font-bold text-brand-600" x-show="rating == 5">Rất hài lòng 😍</p>
                                        <p class="text-sm font-bold text-brand-600" x-show="rating == 4">Hài lòng 😊</p>
                                        <p class="text-sm font-bold text-brand-600" x-show="rating == 3">Bình thường 😐</p>
                                        <p class="text-sm font-bold text-brand-600" x-show="rating == 2">Không hài lòng ☹️</p>
                                        <p class="text-sm font-bold text-brand-600" x-show="rating == 1">Rất kém 😡</p>
                                    </div>

                                    {{-- Comment & Images --}}
                                    <div class="space-y-4">
                                        <textarea name="comment" rows="4" required
                                                  class="w-full px-5 py-4 rounded-2xl border border-gray-200 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/5 transition-all text-sm"
                                                  placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                                        
                                        <div class="relative">
                                            <input type="file" name="images[]" id="review-images" multiple accept="image/*" class="hidden">
                                            <label for="review-images" class="inline-flex items-center gap-3 px-6 py-3 rounded-xl border-2 border-dashed border-gray-200 text-gray-500 hover:border-brand-300 hover:text-brand-600 transition cursor-pointer">
                                                <i class="fas fa-camera text-lg"></i>
                                                <span class="text-sm font-bold">Thêm hình ảnh thực tế</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex gap-4">
                                    <button type="button" @click="reviewModal = false" 
                                            class="flex-1 py-4 rounded-2xl font-bold text-gray-500 hover:bg-gray-200 transition">Hủy bỏ</button>
                                    <button type="submit" 
                                            class="flex-1 py-4 rounded-2xl font-bold bg-brand-600 text-white hover:bg-brand-700 transition shadow-xl shadow-brand-500/30">
                                        Gửi đánh giá
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Status Timeline --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-8 flex items-center gap-3">
                        <div class="w-2 h-8 bg-brand-600 rounded-full"></div>
                        Lịch sử cập nhật
                    </h3>
                    
                    <div class="space-y-8 relative before:absolute before:inset-0 before:left-[15px] before:w-0.5 before:bg-gray-100 before:h-full">
                        @foreach($order->statusHistories as $history)
                            <div class="relative pl-12 group">
                                <div class="absolute left-0 top-1 w-8 h-8 rounded-full bg-white border-4 {{ $loop->last ? 'border-brand-500 animate-pulse' : 'border-gray-100' }} flex items-center justify-center z-10 transition-colors duration-300">
                                    <div class="w-2 h-2 rounded-full {{ $loop->last ? 'bg-brand-500' : 'bg-gray-200' }}"></div>
                                </div>
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 group-hover:border-brand-100 transition duration-300">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-black uppercase text-brand-600 tracking-widest">
                                            {{ \App\Models\Order::getLabel($history->new_status) }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 leading-relaxed font-medium">{{ $history->note }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- RIGHT: Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 sticky top-24">
                    <h2 class="text-xl font-bold text-gray-900 mb-8 uppercase tracking-tight flex items-center justify-between">
                        Thanh toán
                        <i class="fas fa-receipt text-gray-200 text-2xl"></i>
                    </h2>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-gray-500 text-sm font-medium">
                            <span>Tạm tính</span>
                            <span class="text-gray-900">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between text-gray-500 text-sm font-medium">
                            <span>Vận chuyển</span>
                            <span class="text-green-600">{{ $order->shipping_fee > 0 ? number_format($order->shipping_fee, 0, ',', '.') . 'đ' : 'Miễn phí' }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-gray-500 text-sm font-medium">
                                <span>Giảm giá</span>
                                <span class="text-red-500">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                            </div>
                        @endif
                        <div class="pt-6 border-t border-gray-50 mt-6">
                            <div class="flex justify-between items-end">
                                <span class="text-sm font-bold text-gray-900 uppercase">Tổng cộng</span>
                                <div class="text-right">
                                    <p class="text-3xl font-black text-brand-600 leading-none">{{ number_format($order->total_price, 0, ',', '.') }}đ</p>
                                    <p class="text-[10px] text-gray-400 mt-2 font-bold uppercase tracking-widest">Giá đã bao gồm thuế</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($order->note)
                        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 mb-8">
                            <p class="text-xs text-gray-500 font-bold uppercase mb-2">Ghi chú từ bạn:</p>
                            <p class="text-sm text-gray-700 italic leading-relaxed">"{{ $order->note }}"</p>
                        </div>
                    @endif

                    <a href="{{ route('customer.products.index') }}" 
                       class="w-full inline-flex items-center justify-center gap-3 bg-brand-600 text-white py-4 rounded-2xl font-bold hover:bg-brand-700 transition shadow-xl shadow-brand-500/30">
                        <i class="fas fa-shopping-bag"></i> Mua sắm thêm
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div id="cancel-modal" 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
     style="display: none;">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
        <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
            @csrf
            <div class="px-8 py-6 bg-gray-50 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">Huỷ đơn hàng #{{ $order->id }}</h3>
            </div>
            <div class="p-8">
                <p class="text-sm text-gray-500 mb-6">Chúng tôi rất tiếc khi bạn muốn huỷ đơn hàng. Vui lòng cho biết lý do để chúng tôi cải thiện dịch vụ.</p>
                <textarea name="reason" required rows="3"
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 transition outline-none text-sm"
                          placeholder="Lý do huỷ đơn..."></textarea>
            </div>
            <div class="px-8 py-6 bg-gray-50 flex gap-4">
                <button type="button" onclick="document.getElementById('cancel-modal').style.display='none'" 
                        class="flex-1 py-3 rounded-xl font-bold text-gray-500 hover:bg-gray-200 transition">Quay lại</button>
                <button type="submit" class="flex-1 py-3 rounded-xl font-bold bg-red-600 text-white hover:bg-red-700 transition shadow-lg shadow-red-500/30">Xác nhận huỷ</button>
            </div>
        </form>
    </div>
</div>
@endsection
