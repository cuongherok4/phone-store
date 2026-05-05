@extends('admin.layouts.admin')
@section('title', 'Quản lý Nhà Cung Cấp')
@section('page_title', 'Danh sách Nhà Cung Cấp')

@section('content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <form action="{{ route('admin.suppliers.index') }}" method="GET" class="flex items-center space-x-4">
                <input type="text" name="search" placeholder="Tìm kiếm nhà cung cấp..."
                       value="{{ request('search') }}"
                       class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-64">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Tìm kiếm</button>
            </form>
            <a href="{{ route('admin.suppliers.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Thêm mới
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên Nhà Cung Cấp</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thông tin liên hệ</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Hành động</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $supplier->name }}</div>
                                <div class="text-xs text-gray-500">{{ $supplier->address }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $supplier->contact_person }}</div>
                                <div class="text-xs text-gray-500">{{ $supplier->email }}</div>
                                <div class="text-xs text-gray-500">{{ $supplier->phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($supplier->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Tạm dừng
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="text-indigo-600 hover:text-indigo-900">Sửa</a>
                                <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" class="inline ml-4" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Không tìm thấy nhà cung cấp nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $suppliers->links() }}
        </div>
    </div>
@endsection