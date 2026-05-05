@extends('layouts.app')

@section('title', 'Đánh giá sản phẩm - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="tail-container">
        
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Trang chủ</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Viết đánh giá</span>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-10 flex items-center gap-3 uppercase tracking-tight">
            <i class="fas fa-star text-yellow-400"></i>
            Đánh giá sản phẩm đã mua
        </h1>

        <div class="space-y-6">
            @forelse($pendingReviews as $item)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row p-6 md:p-8 gap-8 items-start">
                    
                    {{-- Product Info --}}
                    <div class="w-full md:w-1/3 flex items-center gap-6">
                        <div class="w-24 h-24 bg-gray-50 rounded-2xl flex-shrink-0 border border-gray-100 p-2 flex items-center justify-center">
                            @php
                                $primaryImg = $item->variant->images->where('is_primary', true)->first()?->image_url 
                                           ?? $item->variant->images->first()?->image_url;
                                $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/400x400?text=No+Image';
                            @endphp
                            <img src="{{ $imgUrl }}" class="w-full h-full object-contain mix-blend-multiply">
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-gray-900 mb-1 truncate">{{ $item->name }}</h3>
                            <p class="text-xs text-gray-500">Mua ngày: {{ $item->order->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    {{-- Review Form --}}
                    <div class="flex-1 w-full">
                        <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" x-data="{ rating: 5 }">
                            @csrf
                            <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                            
                            {{-- Star Rating --}}
                            <div class="mb-6">
                                <p class="text-sm font-bold text-gray-700 mb-2">Đánh giá của bạn:</p>
                                <div class="flex gap-2">
                                    <template x-for="i in 5">
                                        <button type="button" @click="rating = i" class="text-2xl transition"
                                                :class="i <= rating ? 'text-yellow-400' : 'text-gray-200'">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </template>
                                    <input type="hidden" name="rating" :value="rating">
                                    <span class="ml-4 text-sm font-bold text-gray-400" x-text="['Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Rất tốt'][rating-1]"></span>
                                </div>
                            </div>

                            {{-- Comment --}}
                            <div class="mb-6">
                                <textarea name="comment" rows="4" required
                                          class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none text-sm"
                                          placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                            </div>

                            {{-- Image Upload (Optional) --}}
                            <div class="mb-8">
                                <label class="inline-flex items-center gap-2 cursor-pointer bg-gray-50 hover:bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl border border-dashed border-gray-300 transition text-sm">
                                    <i class="fas fa-camera"></i> Thêm ảnh thực tế
                                    <input type="file" name="images[]" multiple class="hidden" accept="image/*">
                                </label>
                                <p class="text-[10px] text-gray-400 mt-2 italic">Tối đa 5 ảnh, định dạng JPG/PNG</p>
                            </div>

                            <button type="submit" 
                                    class="bg-brand-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                                Gửi đánh giá
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-gray-100">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-4xl text-gray-200"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Đã hoàn thành tất cả đánh giá</h2>
                    <p class="text-gray-500 mb-8">Cảm ơn bạn đã đóng góp ý kiến để Phone Store ngày càng hoàn thiện hơn!</p>
                    <a href="{{ route('orders.index') }}" 
                       class="inline-flex items-center gap-2 bg-brand-600 text-white px-8 py-3 rounded-full font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                        Xem lịch sử đơn hàng
                    </a>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
