@extends('admin.layouts.admin')

@section('title', 'Quản lý Nhà cung cấp')
@section('page_title', 'Quản lý Nhà cung cấp')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Trang chủ</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">Nhà cung cấp</span>
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

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <form action="{{ route('admin.suppliers.index') }}" method="GET" class="w-full md:w-96 relative">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Tìm tên, email, số điện thoại..." 
                   class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </form>

        <a href="{{ route('admin.suppliers.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition whitespace-nowrap">
            <i class="fas fa-plus"></i> Thêm nhà cung cấp
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên & Liên hệ</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thông tin</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Địa chỉ</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Trạng thái</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->id }}</td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800 text-base">{{ $supplier->name }}</div>
                            @if($supplier->contact_person)
                                <div class="text-xs text-gray-500 italic">Người LH: {{ $supplier->contact_person }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                @if($supplier->email)
                                    <div class="text-sm text-gray-600 flex items-center gap-2">
                                        <i class="fas fa-envelope text-gray-400 w-4"></i>
                                        {{ $supplier->email }}
                                    </div>
                                @endif
                                @if($supplier->phone)
                                    <div class="text-sm text-gray-600 flex items-center gap-2">
                                        <i class="fas fa-phone text-gray-400 w-4"></i>
                                        {{ $supplier->phone }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 line-clamp-2 max-w-xs" title="{{ $supplier->address }}">
                                {{ $supplier->address ?: '---' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($supplier->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Tạm dừng
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}"
                                   class="text-blue-600 hover:text-blue-900 transition" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-truck-loading text-4xl mb-3 block"></i>
                            Chưa có nhà cung cấp nào.
                            <a href="{{ route('admin.suppliers.create') }}" class="text-blue-500 hover:underline ml-1">Thêm ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $suppliers->links() }}
    </div>

@endsection
