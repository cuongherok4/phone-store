<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') — Phone Store</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen flex">
        <aside class="w-80 bg-slate-950 text-slate-100 flex flex-col justify-between">
            <div class="px-6 py-7">
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : route('admin.products.index') }}" class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center text-white text-lg">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div>
                        <div class="text-sm uppercase tracking-[0.2em] text-slate-400">PhoneStore</div>
                        <div class="font-semibold text-lg">Admin Panel</div>
                    </div>
                </a>

                <div class="space-y-1 text-sm">
                    <div class="mb-3 text-xs uppercase tracking-[0.3em] text-slate-500">Sản phẩm</div>
                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 {{ request()->routeIs('admin.products.*') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
                        <i class="fas fa-box-open w-5"></i>
                        <span>Sản phẩm</span>
                    </a>
                    <a href="{{ Route::has('admin.brands.index') ? route('admin.brands.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 {{ request()->routeIs('admin.brands.*') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
                        <i class="fas fa-tags w-5"></i>
                        <span>Thương hiệu</span>
                    </a>
                </div>

                <div class="mt-8 space-y-1 text-sm">
                    <div class="mb-3 text-xs uppercase tracking-[0.3em] text-slate-500">Bán hàng</div>
                    <a href="{{ Route::has('admin.orders.index') ? route('admin.orders.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 {{ request()->routeIs('admin.orders.*') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
                        <i class="fas fa-receipt w-5"></i>
                        <span>Đơn hàng</span>
                    </a>
                    <a href="{{ Route::has('admin.coupons.index') ? route('admin.coupons.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 {{ request()->routeIs('admin.coupons.*') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
                        <i class="fas fa-ticket-alt w-5"></i>
                        <span>Coupon</span>
                    </a>
                </div>

                <div class="mt-8 space-y-1 text-sm">
                    <div class="mb-3 text-xs uppercase tracking-[0.3em] text-slate-500">Kho hàng</div>
                    <a href="{{ Route::has('admin.inventory.index') ? route('admin.inventory.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 {{ request()->routeIs('admin.inventory.*') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
                        <i class="fas fa-warehouse w-5"></i>
                        <span>Tồn kho</span>
                    </a>
                    <a href="{{ Route::has('admin.inventory.logs') ? route('admin.inventory.logs') : '#' }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 {{ request()->routeIs('admin.inventory.*') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
                        <i class="fas fa-history w-5"></i>
                        <span>Lịch sử</span>
                    </a>
                </div>

                <div class="mt-8 space-y-1 text-sm">
                    <div class="mb-3 text-xs uppercase tracking-[0.3em] text-slate-500">Khác</div>
                    <a href="{{ Route::has('admin.users.index') ? route('admin.users.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
                        <i class="fas fa-users w-5"></i>
                        <span>Người dùng</span>
                    </a>
                    <a href="{{ Route::has('admin.reviews.index') ? route('admin.reviews.index') : '#' }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 {{ request()->routeIs('admin.reviews.*') ? 'bg-slate-800 text-white' : 'text-slate-300' }}">
                        <i class="fas fa-star w-5"></i>
                        <span>Đánh giá</span>
                    </a>
                </div>
            </div>

            <div class="px-6 pb-6 pt-4 border-t border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-lg">{{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}</div>
                    <div>
                        <div class="text-sm font-semibold">{{ auth()->user()?->name ?? 'Administrator' }}</div>
                        <div class="text-xs text-slate-400">Administrator</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">
                        <i class="fas fa-sign-out-alt"></i>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-h-screen flex flex-col">
            <header class="bg-white border-b border-slate-200">
                <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.3em] text-slate-500">Admin Dashboard</div>
                        <h1 class="text-2xl font-semibold text-slate-900">@yield('title')</h1>
                        @hasSection('subtitle')
                            <p class="mt-1 text-sm text-slate-500">@yield('subtitle')</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        @yield('header_actions')
                    </div>
                </div>
            </header>

            <main class="flex-1 max-w-7xl mx-auto px-6 py-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
