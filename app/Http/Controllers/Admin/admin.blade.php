<!DOCTYPE html>
<html lang="vi">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Admin Panel - Phone Store</title>
 @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
 <div class="flex h-screen">
 <!-- Sidebar -->
 <aside class="w-64 bg-gray-800 text-white p-4">
 <h1 class="text-2xl font-bold mb-6">Admin Panel</h1>
 <nav>
 <ul>
 <li class="mb-2">
 <a href="{{ route('admin.dashboard') }}" class="block py-2 px-4 rounded hover:bg-gray-700">Dashboard</a>
 </li>
 <li class="mb-2">
 <a href="{{ route('admin.danh-muc.index') }}" class="block py-2 px-4 rounded hover:bg-gray-700">Danh mục</a>
 </li>
 <li class="mb-2">
 <a href="{{ route('admin.thuong-hieu.index') }}" class="block py-2 px-4 rounded hover:bg-gray-700">Thương hiệu</a>
 </li>
 <li class="mb-2">
 <a href="{{ route('admin.suppliers.index') }}" class="block py-2 px-4 rounded hover:bg-gray-700">Nhà cung cấp</a>
 </li>
 {{-- Thêm các mục menu admin khác tại đây --}}
 </ul>
 </nav>
 </aside>

 <!-- Main content -->
 <div class="flex-1 flex flex-col overflow-hidden">
 <!-- Topbar -->
 <header class="bg-white shadow p-4 flex justify-between items-center">
 <h2 class="text-xl font-semibold">@yield('title', 'Admin')</h2>
 <div>
 <span>{{ Auth::user()->name ?? 'Admin' }}</span>
 <form action="{{ route('logout') }}" method="POST" class="inline ml-4">
 @csrf
 <button type="submit" class="text-red-500 hover:text-red-700">Đăng xuất</button>
 </form>
 </div>
 </header>

 <!-- Page content -->
 <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-4">
 @if (session('success'))
 <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
 <span class="block sm:inline">{{ session('success') }}</span>
 </div>
 @endif
 @if (session('error'))
 <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
 <span class="block sm:inline">{{ session('error') }}</span>
 </div>
 @endif
 @yield('content')
 </main>
 </div>
 </div>
</body>
</html>
