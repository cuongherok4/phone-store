@extends('admin.layouts.admin')

@section('title', 'Quản lý người dùng')
@section('page_title', 'Danh sách khách hàng')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Người dùng</span>
@endsection

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Search --}}
    <div class="p-8 border-b border-gray-50">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex items-center gap-4 max-w-md">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Tìm theo tên, email, SĐT..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-brand-500 outline-none transition">
            </div>
            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-800 transition">
                Tìm
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Khách hàng</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Liên hệ</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày tham gia</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $user->name }}</p>
                                    <p class="text-[10px] text-gray-500">ID: #{{ $user->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <p class="text-sm text-gray-700">{{ $user->email }}</p>
                            <p class="text-xs text-gray-500">{{ $user->phone }}</p>
                        </td>
                        <td class="px-8 py-5 text-sm text-gray-500">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-8 py-5">
                            @if($user->deleted_at)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-50 text-red-600">Đã khoá</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-green-50 text-green-600">Hoạt động</span>
                            @endif
                        </td>
                        <td class="px-8 py-5 text-right">
                            <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn thực hiện hành động này?')">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition
                                        {{ $user->deleted_at ? 'bg-green-50 text-green-600 hover:bg-green-600 hover:text-white' : 'bg-red-50 text-red-600 hover:bg-red-600 hover:text-white' }}">
                                    <i class="fas {{ $user->deleted_at ? 'fa-unlock' : 'fa-lock' }}"></i>
                                    {{ $user->deleted_at ? 'Mở khoá' : 'Khoá tài khoản' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-gray-400 italic">Không tìm thấy người dùng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-8 py-6 border-t border-gray-50">
        {{ $users->links() }}
    </div>
</div>
@endsection
