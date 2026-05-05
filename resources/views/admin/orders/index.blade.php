@extends('admin.layouts.admin')

@section('title', 'Quản lý đơn hàng')
@section('page_title', 'Danh sách đơn hàng')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Đơn hàng</span>
@endsection

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Filters --}}
    <div class="p-8 border-b border-gray-50">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Tìm theo mã đơn, khách hàng..."
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none">
            </div>
            <div class="w-48">
                <select name="status" onchange="this.form.submit()"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-brand-500 outline-none">
                    <option value="">Tất cả trạng thái</option>
                    @foreach(['PENDING', 'CONFIRMED', 'SHIPPING', 'COMPLETED', 'CANCELLED'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-800 transition">
                Lọc
            </button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.orders.index') }}" class="px-6 py-2.5 text-gray-500 font-bold text-sm hover:text-gray-900 transition">
                    Xoá lọc
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã đơn</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Khách hàng</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Thanh toán</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-8 py-5">
                            <span class="font-bold text-brand-600">#{{ $order->id }}</span>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="px-8 py-5">
                            <p class="text-sm font-bold text-gray-900">{{ $order->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $order->user->phone }}</p>
                        </td>
                        <td class="px-8 py-5">
                            <p class="text-sm font-bold text-gray-900">{{ number_format($order->total_price, 0, ',', '.') }}đ</p>
                            <p class="text-[10px] text-gray-400">{{ $order->items->count() }} sản phẩm</p>
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_status === 'PAID' ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                                {{ $order->payment_status }}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            @php
                                $statusClasses = [
                                    'PENDING' => 'bg-amber-50 text-amber-600',
                                    'CONFIRMED' => 'bg-blue-50 text-blue-600',
                                    'SHIPPING' => 'bg-indigo-50 text-indigo-600',
                                    'COMPLETED' => 'bg-green-50 text-green-600',
                                    'CANCELLED' => 'bg-red-50 text-red-600',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusClasses[$order->status] ?? 'bg-gray-50' }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <a href="{{ route('admin.orders.show', $order->id) }}" 
                               class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-brand-50 text-brand-600 hover:bg-brand-600 hover:text-white transition shadow-sm">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-8 py-20 text-center text-gray-400 italic">Không tìm thấy đơn hàng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="px-8 py-6 border-t border-gray-50">
        {{ $orders->links() }}
    </div>
</div>
@endsection
