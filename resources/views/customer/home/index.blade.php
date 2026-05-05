@extends('layouts.app')

@section('title', 'Phone Store - Mua sắm thông minh')
@section('description', 'Mua điện thoại chính hãng, giá tốt nhất tại Phone Store. iPhone, Samsung, Xiaomi và nhiều thương hiệu khác.')

@section('content')

    {{-- HERO SECTION (Slider) --}}
    <section class="bg-white pb-10">
        <div class="tail-container pt-6">
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Main Slider --}}
                <div class="w-full {{ $secondaryBanners->count() > 0 ? 'md:w-2/3 lg:w-3/4' : 'w-full' }} rounded-2xl overflow-hidden relative group"
                     x-data="{
                        activeSlide: 0,
                        slides: @js($mainBanners->count() > 0 ? $mainBanners->map(fn($b) => [
                            'img' => asset('storage/' . $b->image_url),
                            'title' => $b->title,
                            'link' => $b->link_url ?? route('customer.products.index')
                        ]) : [
                            ['img' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?q=80&w=1200&auto=format&fit=crop', 'title' => 'Chào mừng đến với Phone Store', 'link' => route('customer.products.index')]
                        ]),
                        autoPlay: null,
                        startAuto() { if(this.slides.length > 1) this.autoPlay = setInterval(() => this.activeSlide = (this.activeSlide + 1) % this.slides.length, 5000); },
                        stopAuto()  { clearInterval(this.autoPlay); }
                     }"
                     x-init="startAuto()"
                     @mouseenter="stopAuto()" @mouseleave="startAuto()">
                    {{-- Images --}}
                    <div class="relative aspect-[21/9] md:aspect-[2.5/1]">
                        <template x-for="(slide, index) in slides" :key="index">
                            <div x-show="activeSlide === index"
                                 x-transition:enter="transition-opacity duration-700"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 class="absolute inset-0">
                                <a :href="slide.link">
                                    <img :src="slide.img" :alt="slide.title" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-r from-gray-900/60 to-transparent"></div>
                                    <div class="absolute inset-y-0 left-0 p-8 md:p-12 flex flex-col justify-center max-w-lg text-white">
                                        <h2 class="text-3xl md:text-5xl font-bold mb-4 leading-tight" x-text="slide.title"></h2>
                                        <span class="bg-white text-gray-900 hover:bg-brand-50 px-8 py-3 rounded-full font-bold w-max transition text-sm">Xem ngay</span>
                                    </div>
                                </a>
                            </div>
                        </template>

                        {{-- Arrows --}}
                        <template x-if="slides.length > 1">
                            <div class="contents">
                                <button @click="activeSlide = (activeSlide - 1 + slides.length) % slides.length"
                                        class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/30 hover:bg-white/60 text-white rounded-full flex items-center justify-center backdrop-blur opacity-0 group-hover:opacity-100 transition z-10">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button @click="activeSlide = (activeSlide + 1) % slides.length"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/30 hover:bg-white/60 text-white rounded-full flex items-center justify-center backdrop-blur opacity-0 group-hover:opacity-100 transition z-10">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </template>

                        {{-- Dots --}}
                        <template x-if="slides.length > 1">
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                                <template x-for="(slide, index) in slides" :key="index">
                                    <button @click="activeSlide = index"
                                            :class="activeSlide === index ? 'w-8 bg-white' : 'w-2 bg-white/50'"
                                            class="h-2 rounded-full transition-all duration-300"></button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Side Banners --}}
                @if($secondaryBanners->count() > 0)
                <div class="w-full md:w-1/3 lg:w-1/4 flex flex-col gap-6">
                    @foreach($secondaryBanners as $banner)
                    <a href="{{ $banner->link_url ?? route('customer.products.index') }}" class="block flex-1 rounded-2xl overflow-hidden relative group/banner">
                        <img src="{{ asset('storage/' . $banner->image_url) }}"
                             alt="{{ $banner->title }}" class="w-full h-full object-cover group-hover/banner:scale-105 transition duration-500">
                        @if($banner->title)
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-transparent flex flex-col justify-end p-6 text-white">
                            <h3 class="font-bold text-lg mb-1">{{ $banner->title }}</h3>
                        </div>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </section>


    {{-- NEW PRODUCTS --}}
    <section class="py-12 bg-white">
        <div class="tail-container">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 uppercase tracking-tight mb-1">Sản phẩm mới nhất</h2>
                    <p class="text-sm text-gray-500">Cập nhật những thiết bị công nghệ đỉnh cao vừa ra mắt</p>
                </div>
                <a href="{{ route('customer.products.index') }}"
                   class="hidden sm:inline-flex items-center gap-2 text-brand-600 font-medium hover:text-brand-800 transition">
                    Xem tất cả <i class="fas fa-arrow-right text-sm"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @forelse($newProducts as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400">
                        <i class="fas fa-box-open text-4xl mb-3"></i>
                        <p>Đang cập nhật sản phẩm mới.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8 text-center sm:hidden">
                <a href="{{ route('customer.products.index') }}"
                   class="inline-flex items-center justify-center w-full bg-brand-50 text-brand-600 font-medium py-3 rounded-xl hover:bg-brand-100 transition">
                    Xem tất cả sản phẩm
                </a>
            </div>
        </div>
    </section>

    {{-- PROMO BANNER --}}
    <section class="py-6 bg-white">
        <div class="tail-container">
            <a href="{{ route('customer.products.index') }}" class="block w-full rounded-2xl overflow-hidden relative group">
                <img src="https://images.unsplash.com/photo-1616423640778-28d1b53229bd?q=80&w=1200&auto=format&fit=crop"
                     alt="Đại tiệc công nghệ" class="w-full h-48 md:h-64 object-cover group-hover:scale-105 transition duration-700">
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                    <div class="text-center text-white">
                        <h3 class="text-3xl md:text-5xl font-bold mb-3">Đại tiệc công nghệ</h3>
                        <p class="text-lg md:text-xl font-medium">Giảm giá lên đến 50% cho toàn bộ sản phẩm điện thoại</p>
                    </div>
                </div>
            </a>
        </div>
    </section>

    {{-- BEST SELLERS --}}
    <section class="py-12 bg-gray-50">
        <div class="tail-container">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 uppercase tracking-tight mb-1">Sản phẩm bán chạy</h2>
                    <p class="text-sm text-gray-500">Những mẫu điện thoại được khách hàng ưa chuộng nhất</p>
                </div>
                <a href="{{ route('customer.products.index') }}"
                   class="hidden sm:inline-flex items-center gap-2 text-brand-600 font-medium hover:text-brand-800 transition">
                    Xem tất cả <i class="fas fa-arrow-right text-sm"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @forelse($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400">
                        <i class="fas fa-box-open text-4xl mb-3"></i>
                        <p>Đang cập nhật sản phẩm nổi bật.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- WHY CHOOSE US --}}
    <section class="py-16 bg-white border-t border-gray-100">
        <div class="tail-container">
            <h2 class="text-2xl font-bold text-center text-gray-900 uppercase tracking-tight mb-12">Tại sao chọn Phone Store?</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                @foreach([
                    ['icon' => 'fa-shield-alt',    'color' => 'text-green-500',  'bg' => 'bg-green-50',  'title' => 'Hàng chính hãng 100%',    'sub' => 'Cam kết chính hãng, có tem bảo hành'],
                    ['icon' => 'fa-truck',          'color' => 'text-blue-500',   'bg' => 'bg-blue-50',   'title' => 'Giao hàng siêu tốc',      'sub' => 'Nội thành trong 2 giờ, toàn quốc 24h'],
                    ['icon' => 'fa-undo',           'color' => 'text-orange-500', 'bg' => 'bg-orange-50', 'title' => 'Đổi trả 30 ngày',         'sub' => 'Miễn phí đổi trả trong 30 ngày đầu'],
                    ['icon' => 'fa-headset',        'color' => 'text-purple-500', 'bg' => 'bg-purple-50', 'title' => 'Hỗ trợ 24/7',            'sub' => 'Đội ngũ CSKH tận tâm mọi lúc mọi nơi'],
                ] as $item)
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-16 {{ $item['bg'] }} rounded-2xl flex items-center justify-center">
                            <i class="fas {{ $item['icon'] }} text-2xl {{ $item['color'] }}"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">{{ $item['title'] }}</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">{{ $item['sub'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection