@extends('layouts.app')

@section('title', 'Thanh toán - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-12" x-data="checkoutManager()">
    <div class="tail-container">
        
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('cart.index') }}" class="hover:text-brand-600 transition">Giỏ hàng</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Thanh toán</span>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-10 flex items-center gap-3 uppercase tracking-tight">
            <i class="fas fa-credit-card text-brand-600"></i>
            Thông tin thanh toán
        </h1>

        <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
            @csrf
            <input type="hidden" name="address_id" x-model="selectedAddressId">
            @if(request('variant_id'))
                <input type="hidden" name="variant_id" value="{{ request('variant_id') }}">
                <input type="hidden" name="quantity" value="{{ request('quantity', 1) }}">
            @endif
            <div class="flex flex-col lg:flex-row gap-10">
                
                {{-- LEFT: Shipping Info --}}
                <div class="lg:w-2/3 space-y-8">
                    
                    {{-- Section 1: Receiver Info --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-8 py-5 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-7 h-7 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs">1</span>
                                Thông tin người nhận
                            </h2>
                            @auth
                                <button type="button" @click="showAddressModal = true" class="text-xs font-bold text-brand-600 hover:underline">
                                    <i class="fas fa-list-ul mr-1"></i> Chọn từ danh bạ
                                </button>
                            @endauth
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">Họ và tên <span class="text-red-500">*</span></label>
                                    <input type="text" name="shipping_name" x-model="shipping_name" required
                                           @input="saveData()"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none"
                                           placeholder="Nguyễn Văn A">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">Số điện thoại <span class="text-red-500">*</span></label>
                                    <input type="tel" name="shipping_phone" x-model="shipping_phone" required
                                           @input="saveData()"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none"
                                           placeholder="0912 xxx xxx">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 col-span-full">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-bold text-gray-700">Tỉnh / Thành phố <span class="text-red-500">*</span></label>
                                        <select x-model="selectedProvinceCode" @change="onProvinceChange" required
                                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none bg-white text-sm">
                                            <option value="">Chọn Tỉnh / Thành phố</option>
                                            <template x-for="p in provinces" :key="p.code">
                                                <option :value="p.code" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-bold text-gray-700">Quận / Huyện <span class="text-red-500">*</span></label>
                                        <select x-model="selectedDistrictCode" @change="onDistrictChange" :disabled="!selectedProvinceCode" required
                                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none bg-white text-sm disabled:bg-gray-50 disabled:cursor-not-allowed">
                                            <option value="">Chọn Quận / Huyện</option>
                                            <template x-for="d in districts" :key="d.code">
                                                <option :value="d.code" x-text="d.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-bold text-gray-700">Phường / Xã <span class="text-red-500">*</span></label>
                                        <select x-model="selectedWardCode" @change="updateFullAddress" :disabled="!selectedDistrictCode" required
                                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none bg-white text-sm disabled:bg-gray-50 disabled:cursor-not-allowed">
                                            <option value="">Chọn Phường / Xã</option>
                                            <template x-for="w in wards" :key="w.code">
                                                <option :value="w.code" x-text="w.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-span-full space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">Địa chỉ cụ thể <span class="text-red-500">*</span></label>
                                    <textarea name="address_detail" x-model="addressDetail" @input="updateFullAddress" required rows="2"
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none"
                                              placeholder="Số nhà, tên đường..."></textarea>
                                    <input type="hidden" name="shipping_address" x-model="shipping_address">
                                </div>
                                <div class="col-span-full space-y-2">
                                    <label class="block text-sm font-bold text-gray-700">Ghi chú (tuỳ chọn)</label>
                                    <textarea name="note" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none"
                                              placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Payment Method --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-8 py-5 bg-gray-50/50 border-b border-gray-100">
                            <h2 class="font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-7 h-7 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs">2</span>
                                Phương thức thanh toán
                            </h2>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="relative flex items-center p-4 border-2 rounded-2xl cursor-pointer transition"
                                       :class="payment_method === 'COD' ? 'border-brand-600 bg-brand-50/30' : 'border-gray-100 hover:border-brand-200'">
                                    <input type="radio" name="payment_method" value="COD" x-model="payment_method" class="hidden">
                                    <div class="flex flex-col items-center gap-2 w-full">
                                        <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
                                        <span class="text-sm font-bold text-gray-900">Thanh toán khi nhận hàng (COD)</span>
                                        <span class="text-[10px] text-gray-500">Giao hàng tận nơi, trả tiền mặt</span>
                                    </div>
                                    <div x-show="payment_method === 'COD'" class="absolute top-2 right-2 text-brand-600">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-4 border-2 rounded-2xl cursor-pointer transition"
                                       :class="payment_method === 'VNPAY' ? 'border-brand-600 bg-brand-50/30' : 'border-gray-100 hover:border-brand-200'">
                                    <input type="radio" name="payment_method" value="VNPAY" x-model="payment_method" class="hidden">
                                    <div class="flex flex-col items-center gap-2 w-full">
                                        <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Icon-VNPAY-QR.png" class="h-8 object-contain">
                                        <span class="text-sm font-bold text-gray-900">Thanh toán VNPAY</span>
                                        <span class="text-[10px] text-gray-500">Thẻ ATM / Ngân hàng</span>
                                    </div>
                                    <div x-show="payment_method === 'VNPAY'" class="absolute top-2 right-2 text-brand-600">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Order Summary --}}
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 uppercase tracking-tight">Đơn hàng của bạn</h2>
                        
                        {{-- Items List --}}
                        <div class="space-y-4 mb-6 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($cart->selectedItems as $item)
                                <div class="flex gap-4">
                                    <div class="w-14 h-14 bg-gray-50 rounded-lg flex-shrink-0 border border-gray-100 p-1 flex items-center justify-center">
                                        @php
                                            $primaryImg = $item->variant->images->where('is_primary', true)->first()?->image_url 
                                                       ?? $item->variant->images->first()?->image_url;
                                            $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/400x400?text=No+Image';
                                        @endphp
                                        <img src="{{ $imgUrl }}" class="w-full h-full object-contain mix-blend-multiply">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">{{ $item->variant->product->name }}</p>
                                        <p class="text-[10px] text-gray-500">
                                            {{ $item->variant->variantAttributes->map(fn($va) => $va->attributeValue->value)->implode(' / ') }}
                                        </p>
                                        <div class="flex justify-between items-center mt-1">
                                            <p class="text-xs text-gray-500">SL: {{ $item->quantity }}</p>
                                            <p class="text-sm font-bold text-brand-600">{{ number_format($item->subtotal, 0, ',', '.') }}đ</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr class="border-gray-100 mb-6">

                        {{-- Coupon Input --}}
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Mã giảm giá</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="couponCode" placeholder="Nhập mã ưu đãi"
                                       class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-brand-500 transition">
                                <button type="button" @click="applyCoupon"
                                        class="px-4 py-2 bg-gray-900 text-white rounded-xl text-sm font-bold hover:bg-gray-800 transition">Áp dụng</button>
                            </div>
                            <button type="button" @click="showCouponModal = true" class="mt-2 text-xs text-brand-600 font-bold hover:underline">
                                <i class="fas fa-ticket-alt mr-1"></i> Xem các mã ưu đãi hiện có
                            </button>
                            <p x-show="couponMessage" x-text="couponMessage" class="mt-2 text-xs" :class="couponSuccess ? 'text-green-600' : 'text-red-500'"></p>
                        </div>

                        {{-- Price Calculation --}}
                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between text-gray-500 text-sm">
                                <span>Tạm tính</span>
                                <span class="font-bold text-gray-900">{{ number_format($cart->total, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="flex justify-between text-gray-500 text-sm">
                                <span>Phí vận chuyển</span>
                                <span class="text-green-600 font-bold">Miễn phí</span>
                            </div>
                            <div class="flex justify-between text-gray-500 text-sm" x-show="discount > 0">
                                <span>Giảm giá</span>
                                <span class="text-red-500 font-bold" x-text="'-' + formatPrice(discount) + 'đ'"></span>
                            </div>
                            <hr class="border-gray-100">
                            <div class="flex justify-between items-end">
                                <span class="text-lg font-bold text-gray-900">Tổng cộng</span>
                                <span class="text-2xl font-black text-red-600 leading-none" x-text="formatPrice(totalPrice) + 'đ'"></span>
                            </div>
                        </div>

                        <button type="submit" 
                                class="block w-full bg-brand-600 text-white text-center py-4 rounded-xl font-bold text-lg hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                            Đặt hàng ngay
                        </button>
                    </div>
                </div>

            </div>
        </form>

        {{-- Address Modal --}}
        @auth
        <div x-show="showAddressModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             style="display: none;">
            <div class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl" @click.away="showAddressModal = false">
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">Địa chỉ đã lưu</h3>
                    <button @click="showAddressModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 max-h-[60vh] overflow-y-auto">
                    <div class="space-y-4">
                        @forelse($addresses as $addr)
                            <div class="p-4 border-2 rounded-2xl cursor-pointer transition hover:bg-brand-50/30"
                                 :class="selectedAddressId === {{ $addr->id }} ? 'border-brand-600 bg-brand-50/50' : 'border-gray-100'"
                                 @click="selectAddress({{ json_encode($addr) }})">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-bold text-gray-900">{{ $addr->receiver_name }}</span>
                                    @if($addr->is_default)
                                        <span class="text-[10px] bg-brand-100 text-brand-600 px-2 py-0.5 rounded-full font-bold">Mặc định</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mb-1">{{ $addr->receiver_phone }}</p>
                                <p class="text-xs text-gray-500">{{ $addr->full_address }}</p>
                            </div>
                        @empty
                            <p class="text-center text-gray-400 py-8">Bạn chưa lưu địa chỉ nào.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endauth

        {{-- Coupon Modal --}}
        <div x-show="showCouponModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             style="display: none;">
            <div class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl" @click.away="showCouponModal = false">
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">Mã giảm giá khả dụng</h3>
                    <button @click="showCouponModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-4">
                        @forelse($coupons as $coupon)
                            @php
                                $isEligible = $cart->total >= $coupon->min_order_value;
                            @endphp
                            <div class="p-4 border-2 rounded-2xl transition flex items-center justify-between {{ $isEligible ? 'border-gray-100 hover:border-brand-200 bg-white' : 'border-gray-50 bg-gray-50 opacity-60 grayscale' }}">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 bg-brand-100 text-brand-600 text-[10px] font-bold rounded-lg uppercase tracking-wider">{{ $coupon->code }}</span>
                                        <span class="text-xs font-bold text-gray-900">
                                            Giảm {{ strtoupper($coupon->discount_type) === 'PERCENT' ? ($coupon->discount_value + 0) . '%' : number_format($coupon->discount_value, 0, ',', '.') . 'đ' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-1">{{ $coupon->description }}</p>
                                    @if($coupon->min_order_value > 0)
                                        <p class="text-[10px] text-gray-400">Đơn tối thiểu: {{ number_format($coupon->min_order_value, 0, ',', '.') }}đ</p>
                                    @endif
                                </div>
                                <div>
                                    @if($isEligible)
                                        <button type="button" @click="selectCoupon('{{ $coupon->code }}')" 
                                                class="px-4 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl hover:bg-brand-700 transition shadow-sm shadow-brand-500/20">
                                            Dùng ngay
                                        </button>
                                    @else
                                        <span class="text-[10px] font-bold text-red-400 italic">Chưa đủ điều kiện</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <i class="fas fa-ticket-alt text-4xl text-gray-200 mb-4"></i>
                                <p class="text-gray-400">Hiện không có mã giảm giá nào khả dụng.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutManager', () => ({
            shipping_name: '{{ auth()->user()->name ?? "" }}',
            shipping_phone: '{{ auth()->user()->phone ?? "" }}',
            shipping_address: '{{ $defaultAddress->full_address ?? "" }}',
            payment_method: 'COD',
            
            // Address System
            provinces: [],
            districts: [],
            wards: [],
            selectedProvinceCode: '',
            selectedDistrictCode: '',
            selectedWardCode: '',
            addressDetail: '{{ $defaultAddress->address_detail ?? "" }}',

            showAddressModal: false,
            selectedAddressId: {{ $defaultAddress->id ?? 'null' }},
            showCouponModal: false,

            couponCode: '',
            couponMessage: '',
            couponSuccess: false,
            discount: 0,
            subtotal: {{ $cart->total }},
            
            async init() {
                await this.fetchProvinces();
                await this.loadSavedData();
            },

            async loadSavedData() {
                const saved = JSON.parse(localStorage.getItem('checkout_info'));
                if (saved) {
                    this.shipping_name = saved.name || this.shipping_name;
                    this.shipping_phone = saved.phone || this.shipping_phone;
                    this.selectedProvinceCode = saved.provinceCode || '';
                    this.addressDetail = saved.addressDetail || this.addressDetail;
                    
                    if (this.selectedProvinceCode) {
                        await this.onProvinceChange(true);
                        this.selectedDistrictCode = saved.districtCode || '';
                        if (this.selectedDistrictCode) {
                            await this.onDistrictChange(true);
                            this.selectedWardCode = saved.wardCode || '';
                        }
                    }
                    this.updateFullAddress();
                }
            },

            saveData() {
                const data = {
                    name: this.shipping_name,
                    phone: this.shipping_phone,
                    provinceCode: this.selectedProvinceCode,
                    districtCode: this.selectedDistrictCode,
                    wardCode: this.selectedWardCode,
                    addressDetail: this.addressDetail
                };
                localStorage.setItem('checkout_info', JSON.stringify(data));
            },

            async fetchProvinces() {
                try {
                    const res = await fetch('https://provinces.open-api.vn/api/p/');
                    this.provinces = await res.json();
                } catch (e) { console.error('Error fetching provinces', e); }
            },

            async onProvinceChange(keepValues = false) {
                if (!keepValues) {
                    this.districts = [];
                    this.wards = [];
                    this.selectedDistrictCode = '';
                    this.selectedWardCode = '';
                }
                if (!this.selectedProvinceCode) return;
                try {
                    const res = await fetch(`https://provinces.open-api.vn/api/p/${this.selectedProvinceCode}?depth=2`);
                    const data = await res.json();
                    this.districts = data.districts;
                } catch (e) { console.error('Error fetching districts', e); }
                this.updateFullAddress();
                this.saveData();
            },

            async onDistrictChange(keepValues = false) {
                if (!keepValues) {
                    this.wards = [];
                    this.selectedWardCode = '';
                }
                if (!this.selectedDistrictCode) return;
                try {
                    const res = await fetch(`https://provinces.open-api.vn/api/d/${this.selectedDistrictCode}?depth=2`);
                    const data = await res.json();
                    this.wards = data.wards;
                } catch (e) { console.error('Error fetching wards', e); }
                this.updateFullAddress();
                this.saveData();
            },

            updateFullAddress() {
                const p = this.provinces.find(x => x.code == this.selectedProvinceCode)?.name || '';
                const d = this.districts.find(x => x.code == this.selectedDistrictCode)?.name || '';
                const w = this.wards.find(x => x.code == this.selectedWardCode)?.name || '';
                
                this.shipping_address = [this.addressDetail, w, d, p].filter(Boolean).join(', ');
                this.saveData();
            },

            get totalPrice() {
                return Math.max(0, this.subtotal - this.discount);
            },

            selectAddress(addr) {
                this.selectedAddressId = addr.id;
                this.shipping_name = addr.receiver_name;
                this.shipping_phone = addr.receiver_phone;
                this.shipping_address = `${addr.address_detail}, ${addr.ward}, ${addr.district}, ${addr.province}`;
                this.addressDetail = addr.address_detail;
                this.showAddressModal = false;
            },

            selectCoupon(code) {
                this.couponCode = code;
                this.showCouponModal = false;
                this.applyCoupon();
            },

            async applyCoupon() {
                if (!this.couponCode) return;
                
                try {
                    const response = await fetch('{{ route("checkout.check_coupon") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ code: this.couponCode })
                    });
                    
                    const data = await response.json();
                    this.couponMessage = data.message;
                    this.couponSuccess = data.success;
                    
                    if (data.success) {
                        const coupon = data.coupon;
                        if (coupon.discount_type === 'PERCENT') {
                            this.discount = (this.subtotal * coupon.discount_value) / 100;
                            // Check max discount if needed
                        } else {
                            this.discount = coupon.discount_value;
                        }
                    } else {
                        this.discount = 0;
                    }
                } catch (error) {
                    this.couponMessage = 'Lỗi hệ thống, vui lòng thử lại.';
                    this.couponSuccess = false;
                }
            },

            formatPrice(price) {
                return new Intl.NumberFormat('vi-VN').format(price);
            }
        }));
    });
</script>
@endpush
