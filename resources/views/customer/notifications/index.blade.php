@extends('layouts.app')

@section('title', 'Thông báo của tôi - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="tail-container">
        
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Trang chủ</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Thông báo</span>
        </div>

        <div class="flex items-center justify-between gap-6 mb-10">
            <h1 class="text-3xl font-bold text-gray-900 uppercase tracking-tight flex items-center gap-3">
                <i class="fas fa-bell text-brand-600"></i>
                Thông báo của tôi
            </h1>
            
            @if($notifications->where('is_read', false)->count() > 0)
                <form action="{{ route('notifications.read_all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-brand-600 font-medium hover:underline">
                        Đánh dấu tất cả đã đọc
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            @forelse($notifications as $noti)
                <div class="p-6 border-b border-gray-100 last:border-0 flex gap-5 transition hover:bg-gray-50/50 {{ $noti->is_read ? 'opacity-70' : 'bg-brand-50/20' }}">
                    
                    {{-- Icon based on type --}}
                    <div class="w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center text-xl
                        {{ $noti->type === 'order' ? 'bg-blue-100 text-blue-600' : 'bg-brand-100 text-brand-600' }}">
                        <i class="fas {{ $noti->type === 'order' ? 'fa-box' : 'fa-info-circle' }}"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4 mb-1">
                            <h3 class="font-bold text-gray-900 {{ $noti->is_read ? 'font-medium' : '' }}">{{ $noti->title }}</h3>
                            <span class="text-xs text-gray-400 whitespace-nowrap">{{ $noti->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed mb-3">{{ $noti->body }}</p>
                        
                        @if(!$noti->is_read)
                            <form action="{{ route('notifications.mark_read', $noti->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-brand-600 hover:underline">
                                    Đánh dấu đã đọc
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            @empty
                <div class="p-16 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="far fa-bell-slash text-3xl text-gray-200"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Không có thông báo nào</h2>
                    <p class="text-gray-500">Chúng tôi sẽ gửi thông báo cho bạn khi có cập nhật mới về đơn hàng hoặc khuyến mãi.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $notifications->links() }}
        </div>

    </div>
</div>
@endsection
