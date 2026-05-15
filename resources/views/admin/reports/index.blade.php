@extends('admin.layouts.admin')

@section('title', 'Báo cáo & Xuất dữ liệu')
@section('page_title', 'Báo cáo & Xuất dữ liệu')

@section('content')
<div class="space-y-8">
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Tổng đơn hàng</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Tổng doanh thu</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_revenue']) }}đ</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Đơn hàng hôm nay</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['orders_today']) }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Doanh thu hôm nay</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['revenue_today']) }}đ</p>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Order Export --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-file-invoice text-blue-600"></i>
                    Xuất danh sách Đơn hàng (Excel)
                </h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.reports.orders.export') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-gray-700">Từ ngày</label>
                            <input type="date" name="start_date" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-gray-700">Đến ngày</label>
                            <input type="date" name="end_date" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-gray-700">Trạng thái đơn hàng</label>
                        <select name="status" 
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition bg-white">
                            <option value="all">Tất cả trạng thái</option>
                            <option value="PENDING">Chờ xử lý</option>
                            <option value="CONFIRMED">Đã xác nhận</option>
                            <option value="SHIPPING">Đang giao</option>
                            <option value="DELIVERED">Đã giao</option>
                            <option value="CANCELLED">Đã hủy</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-blue-500/20 transition flex items-center justify-center gap-2">
                        <i class="fas fa-download"></i>
                        Tải file Excel Đơn hàng
                    </button>
                </form>
            </div>
        </div>

        {{-- Inventory Export --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-warehouse text-orange-600"></i>
                    Báo cáo Tồn kho (Excel)
                </h3>
            </div>
            <div class="p-6 flex-1 flex flex-col justify-between">
                <div class="space-y-4">
                    <p class="text-gray-600 text-sm">Xuất danh sách tất cả các biến thể sản phẩm kèm theo số lượng tồn kho hiện tại và giá trị vốn.</p>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-xs text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Theo dõi hàng tồn kho thực tế
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Nhận biết sản phẩm sắp hết hàng (<= 5)
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Thống kê giá trị tồn kho (giá nhập)
                        </li>
                    </ul>
                </div>

                <a href="{{ route('admin.reports.inventory.export') }}"
                   class="mt-8 w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-orange-500/20 transition flex items-center justify-center gap-2 text-center">
                    <i class="fas fa-file-export"></i>
                    Xuất báo cáo Tồn kho ngay
                </a>
            </div>
        </div>

        {{-- Help / Info --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-info-circle text-gray-600"></i>
                    Thông tin thêm
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 text-sm mb-3 font-semibold">Dữ liệu đơn hàng bao gồm:</p>
                        <ul class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                            <li class="flex items-center gap-2"><i class="fas fa-circle text-[6px]"></i> Mã đơn, ngày đặt</li>
                            <li class="flex items-center gap-2"><i class="fas fa-circle text-[6px]"></i> Tên, SĐT khách</li>
                            <li class="flex items-center gap-2"><i class="fas fa-circle text-[6px]"></i> Địa chỉ giao hàng</li>
                            <li class="flex items-center gap-2"><i class="fas fa-circle text-[6px]"></i> Tổng tiền, PTTT</li>
                        </ul>
                    </div>
                    <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl">
                        <p class="text-xs text-blue-800 leading-relaxed">
                            <span class="font-bold italic">Lưu ý:</span> Để đảm bảo hiệu năng, khi xuất báo cáo đơn hàng với số lượng lớn (trên 10,000 đơn), hệ thống có thể mất vài giây để xử lý. Vui lòng không tải lại trang khi đang xuất.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
