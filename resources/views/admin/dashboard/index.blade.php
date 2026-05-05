@extends('admin.layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('admin.orders.index', ['status' => 'COMPLETED']) }}" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition">
                    <i class="fas fa-wallet"></i>
                </div>
                <span class="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-lg">Tổng cộng</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Tổng doanh thu</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_revenue'], 0, ',', '.') }}đ</p>
        </a>

        <a href="{{ route('admin.orders.index') }}" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="text-xs font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded-lg">Tất cả</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Tổng đơn hàng</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_orders']) }}</p>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'PENDING']) }}" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="text-xs font-bold text-amber-500 bg-amber-50 px-2 py-1 rounded-lg">Cần xử lý</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Đơn hàng mới</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['pending_orders']) }}</p>
        </a>

        <a href="{{ route('admin.inventory.index') }}" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <span class="text-xs font-bold text-red-500 bg-red-50 px-2 py-1 rounded-lg">Tồn kho thấp</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Sản phẩm sắp hết</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['low_stock_variants'] ?? 0) }}</p>
        </a>
        <!-- Total Stock Value Card -->
        <a href="{{ route('admin.inventory.index') }}" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition">
                    <i class="fas fa-coins"></i>
                </div>
                <span class="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-lg">Tồn kho</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Tổng giá trị tồn kho</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_stock_value'], 0, ',', '.') }}đ</p>
        </a>
        <!-- VNPAY Total Card -->
        <a href="{{ route('admin.orders.index', ['payment_method' => 'VNPAY']) }}" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition">
                    <i class="fas fa-credit-card"></i>
                </div>
                <span class="text-xs font-bold text-purple-500 bg-purple-50 px-2 py-1 rounded-lg">VNPAY</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Tổng thanh toán VNPAY</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_vnpay'], 0, ',', '.') }}đ</p>
        </a>
        <!-- MOMO Total Card -->
        <a href="{{ route('admin.orders.index', ['payment_method' => 'MOMO']) }}" class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <span class="text-xs font-bold text-orange-500 bg-orange-50 px-2 py-1 rounded-lg">MOMO</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Tổng thanh toán MOMO</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_momo'], 0, ',', '.') }}đ</p>
        </a>
    </div>

    {{-- Charts & Top/Low Stock --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Biểu đồ doanh thu (30 ngày gần nhất)</h3>
            <div class="h-80">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="space-y-8">
            {{-- Top Products --}}
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Top bán chạy</h3>
                <div class="space-y-6">
                    @forelse($topProducts as $product)
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0 mr-4">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">Đã bán: {{ $product->total_sold }}</p>
                            </div>
                            <div class="w-20 bg-gray-100 h-1 rounded-full overflow-hidden">
                                @php 
                                    $max = $topProducts->first()->total_sold ?: 1;
                                    $percent = ($product->total_sold / $max) * 100;
                                @endphp
                                <div class="h-full bg-brand-600" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 italic">Chưa có dữ liệu bán hàng</p>
                    @endforelse
                </div>
            </div>

            {{-- Low Stock List --}}
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Sản phẩm sắp hết</h3>
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="space-y-6">
                    @forelse($lowStockVariants as $variant)
                        <div class="flex items-center justify-between group">
                            <div class="flex-1 min-w-0 mr-4">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $variant->product->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $variant->sku }} • <span class="text-red-600 font-bold">Còn {{ $variant->total_quantity }}</span>
                                </p>
                            </div>
                            <a href="{{ route('admin.inventory.create', ['variant_id' => $variant->id]) }}" 
                               class="w-8 h-8 bg-gray-50 text-gray-400 rounded-lg flex items-center justify-center hover:bg-brand-50 hover:text-brand-600 transition">
                                <i class="fas fa-plus text-xs"></i>
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-green-500 italic">Tồn kho đang ở mức an toàn ✨</p>
                    @endforelse
                </div>
                @if($lowStockVariants->count() > 0)
                    <a href="{{ route('admin.inventory.index') }}" class="block text-center text-xs font-bold text-gray-400 mt-6 hover:text-brand-600 transition uppercase tracking-widest">Xem tất cả kho</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Đơn hàng mới nhất</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-bold text-brand-600 hover:underline">Xem tất cả</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-8 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã đơn</th>
                        <th class="px-8 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Khách hàng</th>
                        <th class="px-8 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                        <th class="px-8 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-8 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày đặt</th>
                        <th class="px-8 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-8 py-4 font-bold text-brand-600">#{{ $order->id }}</td>
                            <td class="px-8 py-4">
                                <p class="text-sm font-bold text-gray-900">{{ $order->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $order->user->email }}</p>
                            </td>
                            <td class="px-8 py-4 font-bold text-gray-900">{{ number_format($order->total_price, 0, ',', '.') }}đ</td>
                            <td class="px-8 py-4">
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
                                    {{ \App\Models\Order::getLabel($order->status) }}
                                </span>
                            </td>
                            <td class="px-8 py-4 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-8 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="w-8 h-8 bg-gray-50 text-gray-400 rounded-lg inline-flex items-center justify-center hover:bg-blue-50 hover:text-blue-600 transition">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($revenueChart['labels']),
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: @json($revenueChart['values']),
                borderColor: '#1e3a8a',
                backgroundColor: 'rgba(30, 58, 138, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return (value / 1000000).toFixed(1) + 'M';
                        }
                    },
                    grid: { color: '#f3f4f6' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endpush
@endsection