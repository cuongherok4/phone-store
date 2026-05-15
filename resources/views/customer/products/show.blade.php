@extends('layouts.app')

@section('title', $product->name . ' - Phone Store')

@section('content')

<div class="bg-gray-50 py-8"
     x-data="productDetail({
        variants: {{ json_encode($variantsData) }},
        attributesData: {{ json_encode($attributesData) }},
        initialVariantId: {{ $variantsData[0]['id'] ?? 'null' }},
        productPrimaryImage: '{{ $productPrimaryImage }}'
     })">

    <div class="tail-container">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6 flex-wrap">
            <a href="{{ route('home') }}" class="hover:text-brand-600"><i class="fas fa-home"></i> Trang chủ</a>
            <span>/</span>
            <span class="text-gray-900 font-medium line-clamp-1">{{ $product->name }}</span>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 text-green-700 text-sm">
                <i class="fas fa-check-circle text-green-500 text-lg flex-shrink-0"></i>
                {{ session('success') }}
                <a href="{{ route('cart.index') }}" class="ml-auto font-medium underline hover:no-underline">Xem giỏ hàng →</a>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-700 text-sm">
                <i class="fas fa-exclamation-circle text-red-500 text-lg flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Main Product Panel --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
            <div class="flex flex-col md:flex-row gap-10">

                {{-- LEFT: GALLERY --}}
                <div class="w-full md:w-5/12 lg:w-1/2">
                    <div class="relative aspect-square rounded-2xl overflow-hidden border border-gray-100 mb-4 bg-gray-50 flex items-center justify-center p-4">
                        <img :src="currentImage || productPrimaryImage" 
                             alt="{{ $product->name }}"
                             class="w-full h-full object-contain mix-blend-multiply" x-transition>

                        {{-- Discount Badge --}}
                        <template x-if="discountPercent > 0">
                            <div class="absolute top-4 left-4 z-10 bg-red-500 text-white text-sm font-bold px-3 py-1.5 rounded-lg shadow-md">
                                Giảm <span x-text="discountPercent"></span>%
                            </div>
                        </template>
                    </div>

                    {{-- Thumbnails --}}
                    <div class="flex gap-3 overflow-x-auto no-scrollbar pb-2">
                        <template x-for="(img, index) in currentVariantImages" :key="index">
                            <button @click="currentImage = img"
                                    :class="currentImage === img
                                        ? 'border-brand-500 ring-2 ring-brand-200'
                                        : 'border-gray-200 opacity-70 hover:opacity-100'"
                                    class="w-16 h-16 md:w-20 md:h-20 flex-shrink-0 rounded-xl border-2 overflow-hidden transition-all bg-white p-1">
                                <img :src="img" class="w-full h-full object-contain">
                            </button>
                        </template>
                        <template x-if="currentVariantImages.length === 0">
                            <div class="w-16 h-16 rounded-xl border-2 border-gray-100 bg-gray-50 flex items-center justify-center text-gray-300">
                                <i class="fas fa-image"></i>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- RIGHT: PRODUCT DETAIL --}}
                <div class="w-full md:w-7/12 lg:w-1/2 flex flex-col">

                    {{-- Brand + Title --}}
                    @if($product->brand)
                        <a href="{{ route('customer.products.byBrand', $product->brand->slug) }}"
                           class="text-xs font-semibold text-brand-600 uppercase tracking-wider mb-2 hover:underline">
                            {{ $product->brand->name }}
                        </a>
                    @endif
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3 leading-tight">{{ $product->name }}</h1>

                    {{-- Rating + SKU --}}
                    <div class="flex items-center gap-4 mb-5 flex-wrap">
                        <div class="flex items-center gap-1.5 text-sm">
                            <div class="flex text-yellow-400 gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $product->avg_rating)
                                        <i class="fas fa-star text-xs"></i>
                                    @elseif($i - 0.5 <= $product->avg_rating)
                                        <i class="fas fa-star-half-alt text-xs"></i>
                                    @else
                                        <i class="far fa-star text-xs text-gray-300"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="text-brand-600 font-semibold">{{ $product->avg_rating }}</span>
                            <span class="text-gray-400">({{ $product->reviews->count() }} đánh giá)</span>
                        </div>
                        <span class="text-gray-200">|</span>
                        <span class="text-xs text-gray-400">SKU: <span class="font-mono" x-text="currentVariant ? currentVariant.sku : '—'"></span></span>
                    </div>

                    {{-- Price --}}
                    <div class="flex items-end gap-3 mb-6 p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-2xl border border-red-100">
                        <span class="text-3xl font-bold text-red-600"
                              x-text="currentVariant ? currentVariant.formatted_price : 'Liên hệ'"></span>
                        <template x-if="currentVariant && currentVariant.formatted_compare_price">
                            <span class="text-lg text-gray-400 line-through mb-0.5"
                                  x-text="currentVariant.formatted_compare_price"></span>
                        </template>
                        <template x-if="discountPercent > 0">
                            <span class="ml-auto text-xs font-bold text-red-500 bg-red-100 px-2 py-1 rounded-lg">
                                Tiết kiệm <span x-text="discountPercent"></span>%
                            </span>
                        </template>
                    </div>

                    {{-- Variant Selector (Alpine.js) --}}
                    <div class="space-y-5 mb-6">
                        <template x-for="(values, attrName) in attributesData" :key="attrName">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="font-semibold text-gray-900 text-sm" x-text="attrName + ':'"></h3>
                                    <span class="text-sm text-brand-600 font-medium"
                                          x-text="selectedAttributes[attrName] ?? ''"></span>
                                </div>
                                <div class="flex flex-wrap gap-2.5">
                                    <template x-for="val in values" :key="val">
                                        <button @click="selectAttribute(attrName, val)"
                                                :disabled="!isAttributeAvailable(attrName, val)"
                                                :class="{
                                                    'border-brand-600 ring-2 ring-brand-100 bg-brand-50 text-brand-700 font-semibold': isSelected(attrName, val),
                                                    'border-gray-200 bg-white text-gray-700 hover:border-brand-300': !isSelected(attrName, val) && isAttributeAvailable(attrName, val),
                                                    'border-gray-100 bg-gray-100 text-gray-400 cursor-not-allowed opacity-50 line-through': !isAttributeAvailable(attrName, val)
                                                }"
                                                class="px-4 py-2 rounded-xl border-2 transition-all text-sm min-w-[60px]">
                                            <span x-text="val"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Stock Status --}}
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-sm font-medium text-gray-600">Tình trạng:</span>
                        <template x-if="currentVariant && currentVariant.stock > 0">
                            <span class="inline-flex items-center text-sm font-medium text-green-600 bg-green-50 px-3 py-1.5 rounded-full">
                                <i class="fas fa-check-circle mr-1.5"></i>
                                Còn <span x-text="currentVariant.stock" class="mx-1 font-bold"></span> sản phẩm
                            </span>
                        </template>
                        <template x-if="!currentVariant || currentVariant.stock <= 0">
                            <span class="inline-flex items-center text-sm font-medium text-red-600 bg-red-50 px-3 py-1.5 rounded-full">
                                <i class="fas fa-times-circle mr-1.5"></i> Tạm hết hàng
                            </span>
                        </template>
                    </div>

                    {{-- Quantity + Add to Cart --}}
                    <div x-data="{ qty: 1 }">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-sm font-medium text-gray-700">Số lượng:</span>
                            <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                                <button @click="qty = Math.max(1, qty - 1)"
                                        class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition font-bold text-lg">−</button>
                                <input type="number" x-model="qty" min="1"
                                       :max="currentVariant ? currentVariant.stock : 1"
                                       class="w-14 text-center border-0 focus:ring-0 text-sm font-semibold py-2">
                                <button @click="qty = Math.min(currentVariant ? currentVariant.stock : 1, qty + 1)"
                                        :disabled="!currentVariant || qty >= currentVariant.stock"
                                        class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition font-bold text-lg disabled:opacity-40">+</button>
                            </div>
                        </div>

                        {{-- Add to Cart Form --}}
                        <form method="POST" action="{{ route('cart.add') }}" id="addToCartForm" x-ref="cartForm">
                            @csrf
                            <input type="hidden" name="variant_id" :value="currentVariant ? currentVariant.id : ''" x-ref="variantInput">
                            <input type="hidden" name="quantity" :value="qty">

                            <div class="flex gap-3">
                                @auth
                                    <button type="button"
                                            @click="handleAddToCart"
                                            :disabled="!currentVariant || currentVariant.stock <= 0"
                                            :class="{'opacity-50 cursor-not-allowed': !currentVariant || currentVariant.stock <= 0}"
                                            class="flex-1 bg-white border-2 border-brand-600 text-brand-600 hover:bg-brand-50
                                                   font-bold py-3.5 px-6 rounded-xl transition flex items-center justify-center gap-2">
                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                    </button>
                                    <button type="button"
                                            @click="if(currentVariant && currentVariant.stock > 0) { window.location.href = `{{ route('checkout.index') }}?variant_id=${currentVariant.id}&quantity=${qty}`; }"
                                            :disabled="!currentVariant || currentVariant.stock <= 0"
                                            :class="{'opacity-50 cursor-not-allowed shadow-none': !currentVariant || currentVariant.stock <= 0}"
                                            class="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-bold
                                                   py-3.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 transition">
                                        Mua ngay
                                    </button>
                                @else
                                    <a href="{{ route('login') }}"
                                       class="flex-1 bg-white border-2 border-brand-600 text-brand-600 hover:bg-brand-50
                                              font-bold py-3.5 px-6 rounded-xl transition flex items-center justify-center gap-2 text-center">
                                        <i class="fas fa-sign-in-alt"></i> Đăng nhập để mua
                                    </a>
                                    <a href="{{ route('register') }}"
                                       class="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-bold
                                              py-3.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 transition text-center">
                                        Đăng ký ngay
                                    </a>
                                @endauth
                            </div>
                        </form>
                    </div>

                    <hr class="border-gray-100 my-5">

                    {{-- Extra Actions --}}
                    <div class="flex items-center gap-6">
                        <button class="text-sm text-gray-500 hover:text-red-500 transition font-medium flex items-center gap-2">
                            <i class="far fa-heart text-lg"></i> Yêu thích
                        </button>
                        <button onclick="navigator.share ? navigator.share({title:'{{ $product->name }}', url: window.location.href}) : null"
                                class="text-sm text-gray-500 hover:text-brand-600 transition font-medium flex items-center gap-2">
                            <i class="fas fa-share-alt text-lg"></i> Chia sẻ
                        </button>
                        <span class="ml-auto text-sm text-gray-500 font-medium flex items-center gap-2">
                            <i class="fas fa-shield-alt text-lg text-green-500"></i> Bảo hành 12 tháng
                        </span>
                    </div>

                    {{-- Trust badges --}}
                    <div class="grid grid-cols-2 gap-3 mt-5">
                        @foreach([
                            ['icon' => 'fa-truck',        'color' => 'text-blue-500',   'text' => 'Giao hàng toàn quốc'],
                            ['icon' => 'fa-undo',         'color' => 'text-orange-500', 'text' => 'Đổi trả 30 ngày'],
                            ['icon' => 'fa-shield-alt',   'color' => 'text-green-500',  'text' => 'Hàng chính hãng 100%'],
                            ['icon' => 'fa-headset',      'color' => 'text-purple-500', 'text' => 'Hỗ trợ 24/7'],
                        ] as $badge)
                            <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 rounded-xl p-2.5">
                                <i class="fas {{ $badge['icon'] }} {{ $badge['color'] }} w-4 text-center"></i>
                                {{ $badge['text'] }}
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- Description + Reviews --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            {{-- Description --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6 uppercase border-b border-gray-100 pb-4">Đặc điểm nổi bật</h2>
                <div class="prose max-w-none text-gray-700 leading-relaxed">
                    @if($product->short_desc)
                        <div class="mb-6 p-4 bg-brand-50 text-brand-900 rounded-xl font-medium border border-brand-100">
                            {{ $product->short_desc }}
                        </div>
                    @endif
                    @if($product->description)
                        {!! nl2br(e($product->description)) !!}
                    @else
                        <p class="text-gray-400 italic">Đang cập nhật nội dung mô tả sản phẩm.</p>
                    @endif
                </div>

                {{-- Thông số kỹ thuật --}}
                @if($product->specifications && is_array($product->specifications) && count(array_filter($product->specifications)) > 0)
                    <h2 class="text-xl font-bold text-gray-900 mt-10 mb-6 uppercase border-b border-gray-100 pb-4">Thông số kỹ thuật</h2>
                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <tbody>
                                @php
                                    $specLabels = [
                                        'screen' => ['icon' => 'fa-mobile-alt', 'label' => 'Màn hình'],
                                        'chipset' => ['icon' => 'fa-microchip', 'label' => 'Chipset (CPU)'],
                                        'camera_rear' => ['icon' => 'fa-camera', 'label' => 'Camera sau'],
                                        'camera_front' => ['icon' => 'fa-camera-retro', 'label' => 'Camera trước'],
                                        'battery_charging' => ['icon' => 'fa-battery-full', 'label' => 'Pin & Sạc'],
                                    ];
                                @endphp
                                @foreach($specLabels as $key => $data)
                                    @if(!empty($product->specifications[$key]))
                                        <tr class="border-b border-gray-100 last:border-0 odd:bg-gray-50">
                                            <td class="py-3 px-4 font-medium text-gray-700 w-1/3 border-r border-gray-100">
                                                <i class="fas {{ $data['icon'] }} w-5 text-gray-400 mr-1 text-center"></i> {{ $data['label'] }}
                                            </td>
                                            <td class="py-3 px-4 text-gray-900">{{ $product->specifications[$key] }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Reviews --}}
            <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 h-max">
                <h2 class="text-xl font-bold text-gray-900 mb-6 uppercase border-b border-gray-100 pb-4">Đánh giá</h2>

                {{-- Review Summary --}}
                <div class="flex items-center gap-6 mb-8">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-gray-900">{{ number_format($product->reviews->avg('rating') ?? 0, 1) }}</div>
                        <div class="text-yellow-400 text-sm mt-1">
                            @php $avg = $product->reviews->avg('rating') ?? 0; @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $avg ? 'fas' : ($i - 0.5 <= $avg ? 'fas fa-star-half-alt' : 'far') }} fa-star"></i>
                            @endfor
                        </div>
                        <div class="text-xs text-gray-500 mt-1">{{ $product->reviews->count() }} đánh giá</div>
                    </div>
                    <div class="flex-1 space-y-1.5">
                        @for($i = 5; $i >= 1; $i--)
                            @php
                                $count   = $product->reviews->where('rating', $i)->count();
                                $percent = $product->reviews->count() > 0 ? ($count / $product->reviews->count()) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span class="w-3 text-right">{{ $i }}</span>
                                <i class="fas fa-star text-yellow-400 text-[9px]"></i>
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-yellow-400 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="w-4 text-right">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- Quick Review Form (if eligible) --}}
                @if($canReview)
                    <div class="bg-brand-50 rounded-2xl p-5 mb-8 border border-brand-100">
                        <h4 class="font-bold text-brand-900 text-sm mb-3">Bạn đã mua sản phẩm này?</h4>
                        <p class="text-xs text-brand-700 mb-4">Hãy chia sẻ cảm nhận của bạn về sản phẩm nhé!</p>
                        <a href="{{ route('reviews.index') }}" class="inline-block bg-brand-600 text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-brand-700 transition">
                            Viết đánh giá ngay
                        </a>
                    </div>
                @endif

                {{-- Reviews List --}}
                <div class="space-y-6">
                    @forelse($product->reviews as $review)
                        <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                            <div class="flex items-start gap-3 mb-2">
                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(mb_substr($review->user->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-sm text-gray-900">{{ $review->user->name }}</div>
                                        <span class="text-[10px] text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-yellow-400 text-[10px] mt-0.5">
                                        @for($i=1;$i<=5;$i++)<i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>@endfor
                                    </div>
                                </div>
                            </div>
                            @if($review->comment)
                                <p class="text-sm text-gray-600 ml-11 leading-relaxed">{{ $review->comment }}</p>
                            @endif
                            @if($review->images && count($review->images) > 0)
                                <div class="flex gap-2 ml-11 mt-3">
                                    @foreach($review->images as $img)
                                        <img src="{{ asset('storage/' . $img) }}" class="w-12 h-12 object-cover rounded-lg border border-gray-100">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-gray-400 py-10 text-sm bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="far fa-comment-dots text-4xl mb-3 text-gray-200"></i>
                            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Related Products --}}
        @if($relatedProducts->isNotEmpty())
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 uppercase">Sản phẩm liên quan</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-5">
                    @foreach($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productDetail', (data) => ({
            variants: data.variants,
            attributesData: data.attributesData,
            currentVariantId: data.initialVariantId,
            productPrimaryImage: data.productPrimaryImage,
            currentImage: null,
            selectedAttributes: {},

            init() {
                const initial = this.variants.find(v => v.id === this.currentVariantId);
                if (initial) {
                    this.selectedAttributes = { ...initial.attributes };
                    this.updateImageGallery(initial);
                }
            },

            get currentVariant() {
                return this.variants.find(v => v.id === this.currentVariantId) || null;
            },
            get currentVariantImages() {
                const imgs = this.currentVariant?.images ?? [];
                // Nếu variant không có ảnh, hiển thị ít nhất ảnh chính của sản phẩm trong thumbnail
                return imgs.length > 0 ? imgs : [this.productPrimaryImage];
            },
            get discountPercent() {
                if (!this.currentVariant?.compare_price) return 0;
                const p = parseFloat(this.currentVariant.price);
                const c = parseFloat(this.currentVariant.compare_price);
                return c > p ? Math.round(((c - p) / c) * 100) : 0;
            },

            isSelected(attrName, val)       { return this.selectedAttributes[attrName] === val; },
            isAttributeAvailable(attrName, val) { return this.variants.some(v => v.attributes[attrName] === val); },

            selectAttribute(attrName, val) {
                const newSelected = { ...this.selectedAttributes, [attrName]: val };
                const matched = this.findVariantByAttributes(newSelected);
                if (matched) {
                    this.selectedAttributes = newSelected;
                    this.currentVariantId   = matched.id;
                    this.updateImageGallery(matched);
                } else {
                    this.selectedAttributes[attrName] = val;
                    const available = this.variants.filter(v => v.attributes[attrName] === val);
                    if (available.length > 0) {
                        const fallback = available[0];
                        this.selectedAttributes = { ...fallback.attributes };
                        this.currentVariantId   = fallback.id;
                        this.updateImageGallery(fallback);
                    }
                }
            },

            findVariantByAttributes(attrs) {
                return this.variants.find(v => {
                    for (const key in attrs) { if (v.attributes[key] !== attrs[key]) return false; }
                    return true;
                });
            },

            updateImageGallery(variant) {
                this.currentImage = variant.primary_image || this.productPrimaryImage;
            },

            async handleAddToCart() {
                if (!this.currentVariant) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Vui lòng chọn đầy đủ tùy chọn!', type: 'error' } }));
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('variant_id', this.currentVariant.id);
                    formData.append('quantity', this.qty);

                    const response = await fetch('{{ route("cart.add") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Lỗi khi thêm vào giỏ hàng', type: 'error' } }));
                    }
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Đã xảy ra lỗi, vui lòng thử lại sau!', type: 'error' } }));
                }
            }
        }));
    });
</script>
@endpush
