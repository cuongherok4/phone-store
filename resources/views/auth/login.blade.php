@extends('layouts.app')

@section('title', 'Đăng nhập - Phone Store')

@section('content')
<div class="max-w-md mx-auto mt-20 px-4">
    <div class="bg-white rounded-3xl shadow-xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Đăng nhập</h1>
            <p class="text-gray-500 mt-2">Chào mừng bạn quay trở lại</p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500"
                           required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
                    <input type="password" name="password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500"
                           required>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="remember" class="mr-2">
                        Ghi nhớ tôi
                    </label>
                    <a href="#" class="text-blue-600 text-sm hover:underline">Quên mật khẩu?</a>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-2xl transition">
                    Đăng nhập
                </button>

                <div class="relative flex items-center justify-center my-6">
                    <div class="border-t border-gray-200 w-full"></div>
                    <span class="bg-white px-4 text-sm text-gray-500 absolute">Hoặc đăng nhập bằng</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('social.redirect', 'google') }}"
                       class="flex items-center justify-center gap-2 px-4 py-3 border border-gray-200 rounded-2xl hover:bg-gray-50 transition font-medium text-gray-700">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
                        Google
                    </a>
                    <a href="{{ route('social.redirect', 'facebook') }}"
                       class="flex items-center justify-center gap-2 px-4 py-3 border border-gray-200 rounded-2xl hover:bg-gray-50 transition font-medium text-gray-700">
                        <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" class="w-5 h-5" alt="Facebook">
                        Facebook
                    </a>
                </div>
            </div>
        </form>

        <p class="text-center mt-8 text-gray-600">
            Chưa có tài khoản? 
            <a href="{{ route('register') }}" class="text-blue-600 font-medium hover:underline">Đăng ký ngay</a>
        </p>
    </div>
</div>
@endsection