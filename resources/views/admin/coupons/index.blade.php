@extends('admin.layouts.admin')

@section('title', 'Danh sách Mã giảm giá')
@section('page_title', 'Quản lý Coupon')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Coupon</span>
@endsection

@section('content')

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded flex items-center justify-between">
            <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex items-center justify-between">
            <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã hoặc mô tả..."
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">-- Trạng thái --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
                    <i class="fas fa-undo"></i> Bỏ lọc
                </a>
            </div>
        </form>
    </div>

    <div class="flex justify-between items-center mb-6">
        <p class="text-gray-500 text-sm">Hiển thị <strong>{{ $coupons->count() }}</strong> trên tổng <strong>{{ $coupons->total() }}</strong> mã giảm giá</p>
        <a href="{{ route('admin.coupons.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> Tạo mới Coupon
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">STT</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã / Thông tin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mức giảm</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Thời hạn</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Lượt dùng</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-blue-600 tracking-wider">{{ $coupon->code }}</span>
                                    <span class="text-xs text-gray-500 mt-1">{{ $coupon->description ?? 'Không có mô tả' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">
                                @if($coupon->discount_type === 'fixed')
                                    <span class="font-semibold">{{ number_format($coupon->discount_value, 0, ',', '.') }}đ</span>
                                @else
                                    <span class="font-semibold">{{ $coupon->discount_value }}%</span>
                                    @if($coupon->max_discount_amount)
                                        <div class="text-xs text-gray-500 mt-1">Tối đa: {{ number_format($coupon->max_discount_amount, 0, ',', '.') }}đ</div>
                                    @endif
                                @endif
                                @if($coupon->min_order_value)
                                    <div class="text-xs text-gray-500 mt-1">Đơn từ: {{ number_format($coupon->min_order_value, 0, ',', '.') }}đ</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm">
                                @if(!$coupon->start_at && !$coupon->expires_at)
                                    <span class="text-gray-500">Không giới hạn</span>
                                @else
                                    <div class="text-xs text-gray-600">
                                        @if($coupon->start_at)
                                            <div>Từ: {{ $coupon->start_at->format('d/m/Y H:i') }}</div>
                                        @endif
                                        @if($coupon->expires_at)
                                            <div class="mt-1 {{ $coupon->expires_at->isPast() ? 'text-red-500 font-medium' : '' }}">
                                                Đến: {{ $coupon->expires_at->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm">
                                @if($coupon->max_uses)
                                    <span class="font-medium {{ $coupon->used_count >= $coupon->max_uses ? 'text-red-500' : 'text-gray-800' }}">
                                        {{ $coupon->used_count }} / {{ $coupon->max_uses }}
                                    </span>
                                @else
                                    <span class="text-gray-800 font-medium">{{ $coupon->used_count }}</span> <span class="text-gray-500 text-xs">/ ∞</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($coupon->is_active)
                                    @if($coupon->start_at && now()->lt($coupon->start_at))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Chưa bắt đầu
                                        </span>
                                    @elseif(!$coupon->isValid())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Hết hạn / Hết lượt
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Hoạt động
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Tạm dừng
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium">
                                <div class="flex justify-center items-center gap-3">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-blue-600 hover:text-blue-900 transition">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mã giảm giá này? Nếu có đơn hàng đã dùng mã này, việc xóa có thể gây lỗi. Nên ưu tiên Đổi trạng thái sang Tạm dừng.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i class="fas fa-ticket-alt text-4xl mb-3 block"></i>
                                Không tìm thấy mã giảm giá nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $coupons->appends(request()->query())->links() }}
    </div>

@endsection
