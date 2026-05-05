@extends('layouts.app')

@section('title', 'Đăng ký - Phone Store')

@section('content')
<div class="max-w-md mx-auto mt-16 px-4">
    <div class="bg-white rounded-3xl shadow-xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Đăng ký tài khoản</h1>
            <p class="text-gray-500 mt-2">Tạo tài khoản để mua sắm dễ dàng hơn</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="space-y-6">
                <!-- Họ và tên -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Họ và tên</label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500 transition"
                           placeholder="Nguyễn Văn A"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500 transition"
                           placeholder="example@email.com"
                           required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Số điện thoại -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                    <input type="tel" 
                           name="phone" 
                           value="{{ old('phone') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500 transition"
                           placeholder="0123456789">
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mật khẩu -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
                    <input type="password" 
                           name="password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500 transition"
                           required>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nhập lại mật khẩu -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nhập lại mật khẩu</label>
                    <input type="password" 
                           name="password_confirmation"
                           class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-500 transition"
                           required>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-2xl transition duration-200">
                    Đăng ký tài khoản
                </button>
            </div>
        </form>

        <p class="text-center mt-8 text-gray-600">
            Đã có tài khoản? 
            <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">Đăng nhập ngay</a>
        </p>
    </div>
</div>
@endsection