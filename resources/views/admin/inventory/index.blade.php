@extends('admin.layouts.admin')

@section('title', 'Quản lý Tồn kho')
@section('page_title', 'Quản lý Tồn kho')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Tồn kho</span>
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
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo SKU, Tên sản phẩm..."
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="{{ route('admin.inventory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
                    <i class="fas fa-undo"></i> Bỏ lọc
                </a>
            </div>
        </form>
    </div>

    <div class="flex justify-between items-center mb-6">
        <p class="text-gray-500 text-sm">Hiển thị <strong>{{ $inventories->count() }}</strong> trên tổng <strong>{{ $inventories->total() }}</strong> dòng</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.inventory.logs') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
                <i class="fas fa-history"></i> Xem lịch sử
            </a>
            <a href="{{ route('admin.inventory.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Nhập hàng
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">STT</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sản phẩm (SKU)</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng tồn</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventories as $inventory)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-50 rounded-lg border border-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center p-1">
                                    @php
                                        $primaryImg = $inventory->variant->images->where('is_primary', true)->first()?->image_url 
                                                   ?? $inventory->variant->images->first()?->image_url;
                                        $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/100x100?text=No+Img';
                                    @endphp
                                    <img src="{{ $imgUrl }}" class="max-w-full max-h-full object-contain mix-blend-multiply">
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="font-bold text-gray-800 truncate">{{ $inventory->variant->product->name ?? 'N/A' }}</span>
                                    <span class="text-[10px] text-gray-500 font-mono tracking-tighter">{{ $inventory->variant->sku }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            @if($inventory->quantity <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $inventory->quantity }}
                                </span>
                            @elseif($inventory->quantity < 10)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $inventory->quantity }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $inventory->quantity }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-box-open text-4xl mb-3 block"></i>
                            Không tìm thấy dữ liệu tồn kho nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $inventories->appends(request()->query())->links() }}
    </div>

@endsection
