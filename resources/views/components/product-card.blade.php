@use(Illuminate\Support\Str)
@props(['product'])

@php
    $baseVariant = $product->variants->first();
    $hasDiscount = $baseVariant && $baseVariant->compare_price > $baseVariant->price;
    $discountPercent = 0;
    if ($hasDiscount) {
        $discountPercent = round((($baseVariant->compare_price - $baseVariant->price) / $baseVariant->compare_price) * 100);
    }

    // Lấy ảnh từ collection đã eager load — KHÔNG gọi thêm DB query (tránh N+1)
    $imageSrc = null;
    if ($baseVariant && $baseVariant->relationLoaded('images')) {
        $primaryImg = $baseVariant->images->where('is_primary', true)->first();
        $imageSrc   = $primaryImg?->image_url ?? $baseVariant->images->first()?->image_url;
    }
    // Fallback placeholder thông minh nếu chưa có ảnh
    if (!$imageSrc) {
        $imageSrc = 'https://placehold.co/400x400/EBF4FF/1E3A8A?text=' . urlencode(Str::limit($product->name, 20));
    }
    // Nếu là đường dẫn storage tương đối, convert thành URL đầy đủ
    if ($imageSrc && !str_starts_with($imageSrc, 'http')) {
        $imageSrc = asset('storage/' . ltrim($imageSrc, '/'));
    }

    // Rating từ withAvg/withCount (đã eager load — không gọi DB query)
    $avgRating   = round((float) ($product->avg_rating ?? 5.0), 1);
    $reviewCount = (int) ($product->review_count ?? 0);
@endphp

<div class="group bg-white rounded-2xl p-4 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col h-full relative overflow-hidden">

    {{-- Discount Badge --}}
    @if($hasDiscount)
        <div class="absolute top-3 left-3 z-10 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-md shadow">
            -{{ $discountPercent }}%
        </div>
    @endif

    {{-- Product Image --}}
    <a href="{{ route('customer.products.show', $product->slug) }}"
       class="block relative aspect-square mb-4 overflow-hidden rounded-xl bg-gray-50">
        <img src="{{ $imageSrc }}"
             alt="{{ $product->name }}"
             loading="lazy"
             class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-500">

        {{-- Wishlist Button --}}
        @php
            $isWishlisted = false;
            if (auth()->check()) {
                // Ideally this should be eager loaded or coming from a collection
                // For now, we use a simple check but we should optimize this later
                $isWishlisted = auth()->user()->wishlists->contains($product->id);
            }
        @endphp
        <button type="button" 
                x-data="{ wishlisted: {{ $isWishlisted ? 'true' : 'false' }} }"
                @click.stop.prevent="
                    if (!{{ auth()->check() ? 'true' : 'false' }}) { window.location.href = '{{ route('login') }}'; return; }
                    wishlisted = !wishlisted;
                    fetch('{{ route('wishlist.toggle') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ product_id: {{ $product->id }} })
                    }).then(res => res.json()).then(data => {
                        if (!data.success) wishlisted = !wishlisted;
                    });
                }"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full shadow-md flex items-center justify-center transition-all duration-300"
                :class="wishlisted ? 'bg-red-500 text-white' : 'bg-white/80 backdrop-blur text-gray-400 hover:text-red-500'">
            <i class="fa-heart" :class="wishlisted ? 'fas' : 'far'"></i>
        </button>

        {{-- Quick View Button (hover) --}}
        <div class="absolute bottom-3 right-3 w-10 h-10 bg-white/90 backdrop-blur text-brand-600 rounded-full shadow-lg
                    flex items-center justify-center
                    translate-y-12 opacity-0
                    group-hover:translate-y-0 group-hover:opacity-100
                    transition-all duration-300 hover:bg-brand-600 hover:text-white"
             title="Xem chi tiết">
            <i class="fas fa-eye text-sm"></i>
        </div>
    </a>

    {{-- Product Info --}}
    <div class="flex-grow flex flex-col">
        {{-- Category --}}
        @if($product->category)
            <a href="{{ route('customer.products.byCategory', $product->category->slug) }}"
               class="text-xs text-gray-500 hover:text-brand-600 mb-1 font-medium transition line-clamp-1">
                {{ $product->category->name }}
            </a>
        @endif

        {{-- Name --}}
        <a href="{{ route('customer.products.show', $product->slug) }}"
           class="text-sm font-semibold text-gray-800 line-clamp-2 hover:text-brand-600 transition mb-2 leading-snug">
            {{ $product->name }}
        </a>

        {{-- Rating --}}
        <div class="flex items-center gap-1 text-xs mb-3">
            <div class="flex text-yellow-400 gap-0.5">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= floor($avgRating))
                        <i class="fas fa-star text-[10px]"></i>
                    @elseif ($i - 0.5 <= $avgRating)
                        <i class="fas fa-star-half-alt text-[10px]"></i>
                    @else
                        <i class="far fa-star text-[10px] text-gray-300"></i>
                    @endif
                @endfor
            </div>
            <span class="text-gray-500">({{ $reviewCount }})</span>
        </div>

        {{-- Price --}}
        <div class="mt-auto">
            @if($baseVariant)
                <div class="flex items-end gap-2 flex-wrap">
                    <span class="text-base font-bold text-red-600">
                        {{ number_format($baseVariant->price, 0, ',', '.') }}đ
                    </span>
                    @if($hasDiscount)
                        <span class="text-xs text-gray-400 line-through mb-0.5">
                            {{ number_format($baseVariant->compare_price, 0, ',', '.') }}đ
                        </span>
                    @endif
                </div>
                @if($product->variants->count() > 1)
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $product->variants->count() }} phiên bản</p>
                @endif
            @else
                <span class="text-sm text-gray-500 italic">Đang cập nhật giá</span>
            @endif
        </div>
    </div>
</div>
