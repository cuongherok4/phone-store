<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Phone Store')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

    <style>
        [x-cloak] { display: none !important; }
        .admin-sidebar { width: 280px; }
        .nav-link { transition: all 0.2s ease; }
        .nav-link:hover { background-color: #1f2937; transform: translateX(4px); }
        .nav-link-active { background-color: #1f2937; border-left: 4px solid #3b82f6; color: white; }
    </style>
</head>

<body class="bg-gray-100 font-sans">

<div class="flex h-screen overflow-hidden">

    <!-- SIDEBAR -->
    <div class="admin-sidebar bg-gray-900 text-white flex flex-col flex-shrink-0">

        <!-- Logo -->
        <div class="p-6 border-b border-gray-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <i class="fas fa-mobile-alt text-3xl text-blue-500"></i>
                <div>
                    <span class="text-xl font-bold">PhoneStore</span>
                    <p class="text-xs text-gray-400 -mt-1">Admin Panel</p>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">

            <a href="{{ route('admin.dashboard') }}"
               class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : 'text-gray-300' }}">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span>Dashboard</span>
            </a>

            <!-- ── SẢN PHẨM ── -->
            <div class="pt-6">
                <p class="px-4 text-xs uppercase font-semibold tracking-widest text-gray-500 mb-2">Sản phẩm</p>

                @if(Route::has('admin.products.index'))
                <a href="{{ route('admin.products.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.variants.*') ? 'nav-link-active' : 'text-gray-300' }}">
                @else
                <a href="#" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-300">
                @endif
                    <i class="fas fa-box w-5"></i>
                    <span>Sản phẩm</span>
                </a>


                <a href="{{ route('admin.brands.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.brands.*') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-building w-5"></i>
                    <span>Thương hiệu</span>
                </a>

                <a href="{{ route('admin.suppliers.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.suppliers.*') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-truck w-5"></i>
                    <span>Nhà cung cấp</span>
                </a>
            </div>

            <!-- ── BÁN HÀNG ── -->
            <div class="pt-6">
                <p class="px-4 text-xs uppercase font-semibold tracking-widest text-gray-500 mb-2">Bán hàng</p>

                <a href="{{ route('admin.orders.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.orders.*') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Đơn hàng</span>
                </a>

                <a href="{{ route('admin.coupons.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.coupons.*') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-percent w-5"></i>
                    <span>Coupon</span>
                </a>
            </div>

            <!-- ── KHO HÀNG ── -->
            <div class="pt-6">
                <p class="px-4 text-xs uppercase font-semibold tracking-widest text-gray-500 mb-2">Kho hàng</p>

                <a href="{{ route('admin.inventory.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.inventory.index', 'admin.inventory.create') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-warehouse w-5"></i>
                    <span>Tồn kho</span>
                </a>

                <a href="{{ route('admin.inventory.logs') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.inventory.logs') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-history w-5"></i>
                    <span>Lịch sử nhập/xuất</span>
                </a>
            </div>

            <!-- ── KHÁC ── -->
            <div class="pt-6">
                <p class="px-4 text-xs uppercase font-semibold tracking-widest text-gray-500 mb-2">Khác</p>

                <a href="{{ route('admin.users.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>Người dùng</span>
                </a>

                <a href="{{ route('admin.reviews.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.reviews.*') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-star w-5"></i>
                    <span>Đánh giá</span>
                </a>
            </div>

            <!-- ── HỆ THỐNG ── -->
            <div class="pt-6">
                <p class="px-4 text-xs uppercase font-semibold tracking-widest text-gray-500 mb-2">Hệ thống</p>

                <a href="{{ route('admin.banners.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.banners.*') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-image w-5"></i>
                    <span>Banner</span>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                   class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->routeIs('admin.settings.*') ? 'nav-link-active' : 'text-gray-300' }}">
                    <i class="fas fa-cog w-5"></i>
                    <span>Cấu hình</span>
                </a>
            </div>

        </nav>

        <!-- User & Logout -->
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="font-medium text-sm truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400">Administrator</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 py-2.5 rounded-xl text-sm font-medium transition">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </button>
            </form>
        </div>

    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Topbar -->
        <header class="bg-white border-b px-8 py-4 flex items-center justify-between flex-shrink-0">
            <h1 class="text-xl font-semibold text-gray-800">@yield('page_title', 'Trang quản trị')</h1>
            <span class="text-sm text-gray-400">{{ now()->format('d/m/Y') }}</span>
        </header>

        <!-- Breadcrumb -->
        <div class="bg-white border-b px-8 py-2 text-sm text-gray-500 flex-shrink-0">
            @yield('breadcrumb', '<span>Trang chủ</span>')
        </div>

        <!-- Content -->
        <main class="flex-1 overflow-auto p-8">
            @yield('content')
        </main>

    </div>

</div>

@stack('scripts')
</body>
</html>