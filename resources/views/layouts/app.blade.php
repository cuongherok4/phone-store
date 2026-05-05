<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Phone Store - Mua sắm thông minh')</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Tailwind & Alpine --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .tail-container { max-width: 1280px; margin-left: auto; margin-right: auto; padding-left: 1.5rem; padding-right: 1.5rem; }
        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex flex-col min-h-screen">
    
    {{-- TOP BANNER (Optional) --}}
    <div class="bg-brand-900 text-white text-xs py-2">
        <div class="tail-container flex justify-between items-center">
            <div class="flex gap-4">
                <span><i class="fas fa-phone-alt mr-1 text-brand-100"></i> Hotline: {{ $globalSettings['store_phone'] ?? '1900 1234' }}</span>
                <span class="hidden sm:inline"><i class="fas fa-truck mr-1 text-brand-100"></i> Giao hàng toàn quốc</span>
            </div>
            <div class="flex gap-4">
                <a href="#" class="hover:text-brand-100 transition">Kiểm tra đơn hàng</a>
                <a href="#" class="hover:text-brand-100 transition">Hệ thống cửa hàng</a>
            </div>
        </div>
    </div>

    {{-- HEADER --}}
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="tail-container py-4 flex items-center justify-between gap-4 md:gap-8">
            
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="text-2xl md:text-3xl font-bold text-brand-600 flex items-center gap-2 flex-shrink-0">
                <div class="w-10 h-10 bg-brand-600 text-white rounded-xl flex items-center justify-center">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <span class="hidden sm:block tracking-tight">PhoneStore</span>
            </a>

            {{-- Search Bar --}}
            <div class="flex-1 max-w-2xl hidden md:block">
                <form action="{{ route('customer.products.index') }}" method="GET" class="relative group">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Bạn cần tìm điện thoại gì hôm nay?" 
                           class="w-full bg-gray-100 border-transparent focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-200 rounded-full py-2.5 pl-5 pr-12 transition-all outline-none text-sm">
                    <button type="submit" class="absolute right-1 top-1 bottom-1 px-4 text-gray-500 hover:text-brand-600 bg-white rounded-full transition">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            {{-- Icons: Cart & Auth --}}
            <div class="flex items-center gap-5 md:gap-6 flex-shrink-0">
                {{-- Cart --}}
                @php 
                    $cartCount = 0;
                    if (auth()->check()) {
                        $cartCount = app(\App\Services\CartService::class)->getOrCreateCart()->item_count;
                    }
                @endphp
                <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" 
                   class="relative text-gray-600 hover:text-brand-600 transition flex flex-col items-center gap-1"
                   x-data="{ count: {{ $cartCount }} }"
                   @cart-updated.window="count = $event.detail.count">
                    <div class="relative">
                        <i class="fas fa-shopping-cart text-xl md:text-2xl"></i>
                        <span class="absolute -top-1.5 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white min-w-[18px] text-center"
                              x-text="count > 99 ? '99+' : count"
                              x-show="count > 0">
                        </span>
                        <span class="absolute -top-1.5 -right-2 bg-gray-300 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white min-w-[18px] text-center"
                              x-show="count === 0">
                            0
                        </span>
                    </div>
                    <span class="text-[10px] hidden lg:block font-medium">Giỏ hàng</span>
                </a>

                {{-- Notifications --}}
                @auth
                    @php 
                        $unreadCount = auth()->user()->notifications()->where('is_read', false)->count(); 
                    @endphp
                    <a href="{{ route('notifications.index') }}" class="relative text-gray-600 hover:text-brand-600 transition flex flex-col items-center gap-1">
                        <div class="relative">
                            <i class="far fa-bell text-xl md:text-2xl"></i>
                            @if($unreadCount > 0)
                                <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold px-1 py-0.5 rounded-full border-2 border-white min-w-[18px] text-center">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] hidden lg:block font-medium">Thông báo</span>
                    </a>
                @endauth

                {{-- Auth --}}
                @guest
                    <div class="hidden sm:flex items-center gap-3 ml-2 border-l pl-6 border-gray-200">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-brand-600 transition">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="text-sm font-medium bg-brand-600 text-white px-4 py-2 rounded-full hover:bg-brand-700 shadow-sm shadow-brand-500/30 transition">
                            Đăng ký
                        </a>
                    </div>
                    {{-- Mobile User Icon --}}
                    <a href="{{ route('login') }}" class="sm:hidden text-gray-600 hover:text-brand-600 transition">
                        <i class="far fa-user text-xl"></i>
                    </a>
                @else
                    <div class="relative ml-2 border-l pl-6 border-gray-200" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 text-gray-700 hover:text-brand-600 transition">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=EBF4FF&color=1E3A8A" alt="Avatar" class="w-8 h-8 rounded-full border border-gray-200">
                            <div class="hidden md:flex flex-col items-start leading-tight">
                                <span class="text-[10px] text-gray-500">Xin chào,</span>
                                <span class="text-sm font-semibold truncate max-w-[100px]">{{ Auth::user()->name }}</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs ml-1 text-gray-400"></i>
                        </button>
                        
                        {{-- Dropdown --}}
                        <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" 
                             class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600">
                                    <i class="fas fa-tachometer-alt w-5 text-center text-gray-400"></i> Trang Quản Trị
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                            @endif
                            <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600">
                                <i class="far fa-user-circle w-5 text-center text-gray-400"></i> Thông tin tài khoản
                            </a>
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600">
                                <i class="fas fa-clipboard-list w-5 text-center text-gray-400"></i> Đơn hàng của tôi
                            </a>
                            <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600">
                                <i class="fas fa-heart w-5 text-center text-gray-400"></i> Sản phẩm yêu thích
                            </a>
                            <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600">
                                <i class="fas fa-bell w-5 text-center text-gray-400"></i> Thông báo của tôi
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">
                                    <i class="fas fa-sign-out-alt w-5 text-center"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>


        <div class="border-t border-gray-100 hidden lg:block bg-white relative">
            <div class="tail-container">
                <nav class="flex items-center gap-6 py-2">
                        <a href="{{ route('customer.products.index', ['sort' => 'newest']) }}" class="hover:text-brand-600 py-4 border-b-2 border-transparent hover:border-brand-600 transition">Điện thoại mới</a>
                        <a href="{{ route('customer.products.index') }}" class="hover:text-brand-600 py-4 border-b-2 border-transparent hover:border-brand-600 transition text-red-500"><i class="fas fa-bolt text-yellow-400 mr-1"></i> Khuyến mãi Hot</a>
                        <a href="{{ route('customer.products.index') }}" class="hover:text-brand-600 py-4 border-b-2 border-transparent hover:border-brand-600 transition">Giá tốt</a>
                        <a href="{{ route('customer.products.index') }}" class="hover:text-brand-600 py-4 border-b-2 border-transparent hover:border-brand-600 transition">Điện thoại cũ</a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    {{-- MOBILE SEARCH --}}
    <div class="p-4 bg-white md:hidden border-b border-gray-100 sticky top-[72px] z-40">
        <form action="{{ route('customer.products.index') }}" method="GET" class="relative">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Bạn cần tìm điện thoại gì?"
                   class="w-full bg-gray-100 border-transparent focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-200 rounded-lg py-2.5 pl-10 pr-4 outline-none text-sm">
            <div class="absolute left-3 top-2.5 text-gray-400">
                <i class="fas fa-search"></i>
            </div>
        </form>
    </div>

    <!-- MAIN CONTENT -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-300 pt-16 pb-8 mt-16 border-t-4 border-brand-600">
        <div class="tail-container">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                {{-- Col 1 --}}
                <div>
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-white flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 bg-brand-600 text-white rounded flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-sm"></i>
                        </div>
                        PhoneStore
                    </a>
                    <p class="text-sm text-gray-400 mb-6 leading-relaxed">
                        Hệ thống bán lẻ điện thoại di động chính hãng uy tín nhất Việt Nam. Cam kết chất lượng, giá tốt, bảo hành dài lâu.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-brand-600 transition text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-pink-600 transition text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-red-600 transition text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                {{-- Col 2 --}}
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Thông tin hỗ trợ</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-brand-400 transition">Chính sách bảo hành</a></li>
                        <li><a href="#" class="hover:text-brand-400 transition">Chính sách đổi trả</a></li>
                        <li><a href="#" class="hover:text-brand-400 transition">Chính sách giao hàng</a></li>
                        <li><a href="#" class="hover:text-brand-400 transition">Bảo mật thông tin</a></li>
                        <li><a href="#" class="hover:text-brand-400 transition">Hướng dẫn mua trả góp</a></li>
                    </ul>
                </div>

                {{-- Col 3 --}}
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Thương hiệu nổi bật</h4>
                    <ul class="space-y-3 text-sm">
                        @if(isset($globalBrands))
                            @foreach($globalBrands as $brand)
                                <li><a href="{{ route('customer.products.byBrand', $brand->slug) }}" class="hover:text-brand-400 transition">{{ $brand->name }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                {{-- Col 4 --}}
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Liên hệ</h4>
                    <ul class="space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt mt-1 text-brand-500"></i>
                            <span>123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-phone-alt text-brand-500"></i>
                            <span class="font-bold text-white text-lg">{{ $globalSettings['store_phone'] ?? '1900 1234' }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-brand-500"></i>
                            <span>support@phonestore.vn</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} Phone Store. Đã đăng ký bản quyền.</p>
                <div class="flex gap-2">
                    <i class="fab fa-cc-visa text-2xl"></i>
                    <i class="fab fa-cc-mastercard text-2xl"></i>
                    <i class="fab fa-cc-paypal text-2xl"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- TOAST NOTIFICATION -->
    <div x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            showToast(msg, type = 'success') {
                this.message = msg;
                this.type = type;
                this.show = true;
                setTimeout(() => { this.show = false; }, 3000);
            }
         }"
         @toast.window="showToast($event.detail.message, $event.detail.type)"
         x-init="
            @if(session('success')) showToast('{{ session('success') }}', 'success'); @endif
            @if(session('error')) showToast('{{ session('error') }}', 'error'); @endif
            @if($errors->any()) showToast('{{ $errors->first() }}', 'error'); @endif
         "
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-10"
         style="display: none;"
         class="fixed top-24 left-1/2 -translate-x-1/2 z-[100] min-w-[300px]">
        <div :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'" 
             class="text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3">
            <i :class="type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
            <span x-text="message" class="font-bold text-sm"></span>
        </div>
    </div>

    {{-- AI Assistant --}}
    @include('components.ai-assistant')

    @stack('scripts')
    {{-- Alpine.js must be loaded after custom scripts to ensure data objects are registered --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>