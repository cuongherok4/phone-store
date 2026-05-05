@extends('admin.layouts.admin')

@section('title', 'Lịch sử Tồn kho')
@section('page_title', 'Lịch sử Tồn kho')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.inventory.index') }}" class="text-gray-500 hover:text-gray-700">Tồn kho</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Lịch sử</span>
@endsection

@section('content')

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('admin.inventory.logs') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo SKU, Tên sản phẩm..."
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>
            <div class="w-full md:w-48">
                <select name="change_type" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">-- Tất cả giao dịch --</option>
                    <option value="IMPORT" {{ request('change_type') == 'IMPORT' ? 'selected' : '' }}>Nhập hàng (IMPORT)</option>
                    <option value="EXPORT" {{ request('change_type') == 'EXPORT' ? 'selected' : '' }}>Xuất hàng (EXPORT)</option>
                    <option value="ADJUST" {{ request('change_type') == 'ADJUST' ? 'selected' : '' }}>Điều chỉnh (ADJUST)</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="{{ route('admin.inventory.logs') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
                    <i class="fas fa-undo"></i> Bỏ lọc
                </a>
            </div>
        </form>
    </div>

    <div class="flex justify-between items-center mb-6">
        <p class="text-gray-500 text-sm">Hiển thị <strong>{{ $logs->count() }}</strong> trên tổng <strong>{{ $logs->total() }}</strong> dòng</p>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Thời gian</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sản phẩm (SKU)</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Giao dịch</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nhà cung cấp</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Thay đổi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người thực hiện</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                            {{ $log->created_at->format('d/m/Y') }}
                            <div class="text-[10px] text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white rounded-xl border border-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center p-1 shadow-sm">
                                    @php
                                        $primaryImg = $log->variant->images->where('is_primary', true)->first()?->image_url 
                                                   ?? $log->variant->images->first()?->image_url;
                                        $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/100x100?text=No+Img';
                                    @endphp
                                    <img src="{{ $imgUrl }}" class="max-w-full max-h-full object-contain mix-blend-multiply">
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="font-bold text-gray-800 truncate text-sm">{{ $log->variant->product->name ?? 'N/A' }}</span>
                                    <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-mono w-fit">{{ $log->variant->sku }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($log->change_type == 'IMPORT')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    <i class="fas fa-download mr-1.5"></i> NHẬP
                                </span>
                            @elseif($log->change_type == 'EXPORT')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                    <i class="fas fa-upload mr-1.5"></i> XUẤT
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-700 border border-gray-200">
                                    <i class="fas fa-sync-alt mr-1.5"></i> ĐIỀU CHỈNH
                                </span>
                            @endif
                            @if($log->reference_type == 'order' && $log->reference_id)
                                <a href="{{ route('admin.orders.show', $log->reference_id) }}" class="block text-[10px] mt-1 text-blue-500 hover:underline font-bold">
                                    Đơn #{{ $log->reference_id }}
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->supplier)
                                <span class="text-sm text-gray-600 flex items-center gap-1.5">
                                    <i class="fas fa-truck text-gray-400 text-xs"></i>
                                    {{ $log->supplier->name }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex flex-col items-center">
                                @if($log->quantity_change > 0)
                                    <span class="text-green-600 font-extrabold text-base">+{{ $log->quantity_change }}</span>
                                @elseif($log->quantity_change < 0)
                                    <span class="text-red-600 font-extrabold text-base">{{ $log->quantity_change }}</span>
                                @else
                                    <span class="text-gray-500 font-extrabold text-base">0</span>
                                @endif
                                <div class="flex items-center gap-1 text-[10px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full border border-gray-100 mt-1">
                                    <span>{{ $log->quantity_before }}</span>
                                    <i class="fas fa-long-arrow-alt-right"></i>
                                    <span class="font-bold text-gray-600">{{ $log->quantity_after }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-800">{{ $log->createdBy->name ?? 'Hệ thống' }}</span>
                                @if($log->note)
                                    <span class="text-[10px] text-gray-500 italic mt-0.5" title="{{ $log->note }}">
                                        "{{ Str::limit($log->note, 25) }}"
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300">
                                    <i class="fas fa-clipboard-list text-3xl"></i>
                                </div>
                                <p class="text-gray-400 font-medium">Không tìm thấy dữ liệu lịch sử nào.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $logs->appends(request()->query())->links() }}
    </div>

@endsection
