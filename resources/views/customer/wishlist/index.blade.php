@extends('layouts.app')

@section('title', 'Sản phẩm yêu thích - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="tail-container">
        
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Trang chủ</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Sản phẩm yêu thích</span>
        </div>

        <div class="flex items-center justify-between gap-6 mb-10">
            <h1 class="text-3xl font-bold text-gray-900 uppercase tracking-tight flex items-center gap-3">
                <i class="fas fa-heart text-red-500"></i>
                Sản phẩm yêu thích
            </h1>
            <p class="text-gray-500 font-medium hidden md:block">({{ $products->count() }} sản phẩm)</p>
        </div>

        @if($products->isEmpty())
            <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-gray-100">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="far fa-heart text-4xl text-gray-200"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Danh sách trống</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Bạn chưa có sản phẩm nào trong danh sách yêu thích. Hãy khám phá và lưu lại những sản phẩm bạn ưng ý nhé!</p>
                <a href="{{ route('customer.products.index') }}" 
                   class="inline-flex items-center gap-2 bg-brand-600 text-white px-8 py-3 rounded-full font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                    Khám phá ngay
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div id="wishlist-item-{{ $product->id }}">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
