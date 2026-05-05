@extends('admin.layouts.admin')

@section('title', 'Chỉnh sửa Coupon')
@section('page_title', 'Chỉnh sửa Mã giảm giá')

@section('breadcrumb')
    <span>Trang chủ</span>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.coupons.index') }}" class="text-blue-600 hover:text-blue-800">Coupon</a>
    <span class="mx-2">/</span>
    <span class="text-gray-800 font-medium">{{ $coupon->code }}</span>
@endsection

@section('content')

    <div class="bg-white shadow rounded-lg p-6 max-w-4xl mx-auto">
        
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <span class="text-red-700 font-medium">Vui lòng kiểm tra lại thông tin bên dưới:</span>
                </div>
                <ul class="mt-2 ml-7 list-disc text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" x-data="couponForm()">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Mã Coupon --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã giảm giá (Code) <span class="text-red-500">*</span></label>
                    <div class="flex">
                        <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required
                               class="w-full border-gray-300 rounded-l-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 uppercase"
                               placeholder="VD: SUMMER2024, TET50K...">
                        <button type="button" @click="generateCode()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-r-lg font-medium border border-l-0 border-gray-300 transition">
                            Tạo ngẫu nhiên
                        </button>
                    </div>
                </div>

                {{-- Mô tả --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả hiển thị</label>
                    <textarea name="description" rows="2"
                              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                              placeholder="Mô tả ngắn gọn về chương trình...">{{ old('description', $coupon->description) }}</textarea>
                </div>

                {{-- Loại giảm giá & Mức giảm --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại giảm giá <span class="text-red-500">*</span></label>
                    <select name="discount_type" x-model="discountType"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="fixed">Giảm số tiền cố định (VNĐ)</option>
                        <option value="percent">Giảm theo phần trăm (%)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mức giảm <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="discount_value" value="{{ old('discount_value', floatval($coupon->discount_value)) }}" required min="0" step="any"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 pr-10"
                               placeholder="Nhập mức giảm...">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm font-medium" x-text="discountType === 'percent' ? '%' : 'VNĐ'"></span>
                        </div>
                    </div>
                </div>

                {{-- Mức giảm tối đa (Chỉ hiện khi giảm theo %) --}}
                <div x-show="discountType === 'percent'" x-cloak class="col-span-1 md:col-span-2 bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số tiền giảm tối đa (VNĐ)</label>
                    <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount', floatval($coupon->max_discount_amount)) }}" min="0"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="Bỏ trống nếu không giới hạn...">
                    <p class="text-xs text-gray-500 mt-1">Giới hạn số tiền giảm được khi dùng mã phần trăm.</p>
                </div>

                {{-- Điều kiện đơn tối thiểu --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đơn hàng tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_value" value="{{ old('min_order_value', floatval($coupon->min_order_value)) }}" min="0"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="text-xs text-gray-500 mt-1">Giá trị đơn hàng (tạm tính) tối thiểu để được áp dụng mã. Nhập 0 nếu áp dụng cho mọi đơn.</p>
                </div>

                <div class="col-span-1 md:col-span-2 border-t border-gray-200 my-2"></div>

                {{-- Giới hạn số lượt --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tổng lượt sử dụng tối đa</label>
                    <input type="number" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" min="1"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="Bỏ trống nếu không giới hạn...">
                    <p class="text-xs mt-1 text-blue-600"><i class="fas fa-info-circle"></i> Đã dùng: <b>{{ $coupon->used_count }}</b> lượt</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lượt dùng tối đa mỗi khách hàng</label>
                    <input type="number" name="max_uses_per_user" value="{{ old('max_uses_per_user', $coupon->max_uses_per_user) }}" min="1"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                           placeholder="Mặc định là 1 lượt/người">
                </div>

                {{-- Thời hạn --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu</label>
                    <input type="datetime-local" name="start_at" value="{{ old('start_at', $coupon->start_at ? $coupon->start_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>

                {{-- Trạng thái --}}
                <div class="col-span-1 md:col-span-2 mt-2 bg-gray-50 p-4 rounded-lg">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
                               class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <span class="ml-2 text-gray-800 font-medium">Trạng thái Hoạt động</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-2 ml-7">Bỏ chọn nếu bạn muốn tạm dừng sử dụng mã giảm giá này mà không xóa nó.</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.coupons.index') }}"
                   class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-2.5 rounded-lg font-medium transition">
                    Hủy bỏ
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Cập nhật thay đổi
                </button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('couponForm', () => ({
            discountType: '{{ old('discount_type', $coupon->discount_type) }}',
            
            generateCode() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let result = '';
                for (let i = 0; i < 8; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                document.querySelector('input[name="code"]').value = result;
            }
        }))
    })
</script>
@endpush
