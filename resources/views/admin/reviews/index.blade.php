@extends('admin.layouts.admin')

@section('title', 'Quản lý đánh giá')
@section('page_title', 'Danh sách đánh giá')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Đánh giá</span>
@endsection

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Filters --}}
    <div class="p-8 border-b border-gray-50">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="flex items-center gap-4">
            <select name="status" onchange="this.form.submit()"
                    class="w-48 px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-brand-500 outline-none">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
            </select>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Khách hàng</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Đánh giá</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Nhận xét</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($reviews as $review)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-8 py-5">
                            <p class="text-sm font-bold text-gray-900">{{ $review->user->name }}</p>
                            <p class="text-[10px] text-gray-400">{{ $review->created_at->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="px-8 py-5">
                            <p class="text-sm text-gray-900 font-medium truncate max-w-[200px]">{{ $review->product->name }}</p>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex text-yellow-400 text-xs">
                                @for($i=1;$i<=5;$i++)
                                    <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <p class="text-sm text-gray-600 line-clamp-2 max-w-[300px]">{{ $review->comment }}</p>
                            @if($review->images && count($review->images) > 0)
                                <div class="flex gap-1 mt-2">
                                    @foreach($review->images as $img)
                                        <img src="{{ asset('storage/' . $img) }}" class="w-8 h-8 object-cover rounded border border-gray-100">
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-8 py-5">
                            @if($review->is_approved)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-green-50 text-green-600">Đã duyệt</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-50 text-amber-600">Chờ duyệt</span>
                            @endif
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                @if(!$review->is_approved)
                                    <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Duyệt"
                                                class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-600 hover:text-white transition flex items-center justify-center">
                                            <i class="fas fa-check text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Xoá đánh giá này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Xoá"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition flex items-center justify-center">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-8 py-20 text-center text-gray-400 italic">Không có đánh giá nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-8 py-6 border-t border-gray-50">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
