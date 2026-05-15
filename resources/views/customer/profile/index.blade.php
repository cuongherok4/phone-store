@extends('layouts.app')

@section('title', 'Tài khoản của tôi - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="tail-container">
        
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Trang chủ</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Tài khoản cá nhân</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-8" x-data="{ tab: 'profile' }">
            
            {{-- Sidebar --}}
            <div class="w-full lg:w-1/4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <div class="flex items-center gap-4 mb-8 pb-6 border-b border-gray-50">
                        <div class="relative group">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=EBF4FF&color=1E3A8A' }}" 
                                 class="w-16 h-16 rounded-full object-cover border-2 border-brand-100">
                            <label for="avatar_input" class="absolute inset-0 flex items-center justify-center bg-black/40 text-white rounded-full opacity-0 group-hover:opacity-100 transition cursor-pointer">
                                <i class="fas fa-camera text-xs"></i>
                            </label>
                            <form id="avatar_form" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" id="avatar_input" name="avatar" onchange="document.getElementById('avatar_form').submit()">
                            </form>
                        </div>
                        <div class="min-w-0">
                            <h2 class="font-bold text-gray-900 truncate">{{ $user->name }}</h2>
                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                        </div>
                    </div>

                    <nav class="flex flex-col gap-1">
                        <button @click="tab = 'profile'" :class="tab === 'profile' ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:bg-gray-50'" 
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition">
                            <i class="far fa-user w-5"></i> Thông tin cá nhân
                        </button>
                        <button @click="tab = 'addresses'" :class="tab === 'addresses' ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:bg-gray-50'"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition">
                            <i class="fas fa-map-marker-alt w-5"></i> Sổ địa chỉ
                        </button>
                        <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-clipboard-list w-5"></i> Lịch sử đơn hàng
                        </a>
                        <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition">
                            <i class="far fa-heart w-5"></i> Sản phẩm yêu thích
                        </a>
                        <button @click="tab = 'password'" :class="tab === 'password' ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:bg-gray-50'"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition">
                            <i class="fas fa-key w-5"></i> Đổi mật khẩu
                        </button>
                    </nav>

                    <div class="mt-8 pt-6 border-t border-gray-50">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-red-500 hover:bg-red-50 transition">
                                <i class="fas fa-sign-out-alt w-5"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="flex-1">
                
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl flex items-center gap-3 text-green-700 text-sm">
                        <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm">
                        <ul class="list-disc ml-5">
                            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Tab: Profile --}}
                <div x-show="tab === 'profile'" x-transition>
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-8 pb-4 border-b border-gray-50">Thông tin cá nhân</h2>
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Họ và tên</label>
                                    <input type="text" name="name" value="{{ $user->name }}" required
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Số điện thoại</label>
                                    <input type="text" name="phone" value="{{ $user->phone }}"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Email (Không thể thay đổi)</label>
                                    <input type="email" value="{{ $user->email }}" disabled
                                           class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-500 text-sm cursor-not-allowed">
                                </div>
                            </div>
                            <button type="submit" class="bg-brand-600 text-white px-10 py-3 rounded-xl font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                                Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Tab: Addresses --}}
                <div x-show="tab === 'addresses'" x-transition style="display: none;">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-50">
                            <h2 class="text-xl font-bold text-gray-900">Sổ địa chỉ</h2>
                            <button @click="$dispatch('open-modal', 'add-address')" class="text-sm font-bold text-brand-600 hover:underline">
                                + Thêm địa chỉ mới
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($addresses as $addr)
                                <div class="relative p-6 rounded-2xl border transition-all {{ $addr->is_default ? 'border-brand-500 bg-brand-50/20' : 'border-gray-100 hover:border-brand-200 bg-white' }}">
                                    @if($addr->is_default)
                                        <span class="absolute top-4 right-4 text-[10px] font-bold bg-brand-600 text-white px-2 py-0.5 rounded-md">MẶC ĐỊNH</span>
                                    @endif
                                    
                                    <div class="flex items-center gap-2 mb-3">
                                        <i class="fas fa-user-circle text-brand-400"></i>
                                        <h4 class="font-bold text-gray-900 text-sm">{{ $addr->receiver_name }}</h4>
                                    </div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <i class="fas fa-phone text-brand-400 text-xs"></i>
                                        <span class="text-sm text-gray-700">{{ $addr->receiver_phone }}</span>
                                    </div>
                                    <div class="flex items-start gap-2 mb-5">
                                        <i class="fas fa-map-marker-alt text-brand-400 text-xs mt-1"></i>
                                        <p class="text-sm text-gray-600 leading-snug">{{ $addr->full_address }}</p>
                                    </div>

                                    <div class="flex items-center gap-4 pt-4 border-t border-gray-50">
                                        @if(!$addr->is_default)
                                            <form action="{{ route('profile.address.default', $addr->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-xs font-bold text-brand-600 hover:underline">Thiết lập mặc định</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('profile.address.destroy', $addr->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xoá địa chỉ này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-red-500 hover:underline">Xoá</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Tab: Password --}}
                <div x-show="tab === 'password'" x-transition style="display: none;">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-8 pb-4 border-b border-gray-50">Đổi mật khẩu</h2>
                        <form action="{{ route('profile.password') }}" method="POST" class="max-w-md">
                            @csrf
                            <div class="space-y-6 mb-8">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Mật khẩu hiện tại</label>
                                    <input type="password" name="current_password" required
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Mật khẩu mới</label>
                                    <input type="password" name="password" required
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Xác nhận mật khẩu mới</label>
                                    <input type="password" name="password_confirmation" required
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition outline-none text-sm">
                                </div>
                            </div>
                            <button type="submit" class="bg-brand-600 text-white px-10 py-3 rounded-xl font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                                Cập nhật mật khẩu
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- Add Address Modal (Simple Implementation with Alpine) --}}
<div x-data="{ open: false }" 
     @open-modal.window="if ($event.detail === 'add-address') open = true" 
     x-show="open" 
     style="display: none;"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div @click.away="open = false" class="bg-white rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl">
        <div class="p-6 md:p-8 border-b border-gray-50 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900 uppercase">Thêm địa chỉ mới</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition text-2xl">×</button>
        </div>
        <form action="{{ route('profile.address.store') }}" method="POST" class="p-6 md:p-8">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6" x-data="addressFetcher()">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tên người nhận</label>
                    <input type="text" name="receiver_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Số điện thoại</label>
                    <input type="text" name="receiver_phone" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tỉnh / Thành phố</label>
                    <select name="province_name" x-model="selectedProvinceCode" @change="onProvinceChange" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-white">
                        <option value="">Chọn Tỉnh / Thành phố</option>
                        <template x-for="p in provinces" :key="p.code">
                            <option :value="p.code" x-text="p.name"></option>
                        </template>
                    </select>
                    <input type="hidden" name="province" :value="provinces.find(x => x.code == selectedProvinceCode)?.name || ''">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Quận / Huyện</label>
                    <select name="district_name" x-model="selectedDistrictCode" @change="onDistrictChange" :disabled="!selectedProvinceCode" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-white disabled:bg-gray-50">
                        <option value="">Chọn Quận / Huyện</option>
                        <template x-for="d in districts" :key="d.code">
                            <option :value="d.code" x-text="d.name"></option>
                        </template>
                    </select>
                    <input type="hidden" name="district" :value="districts.find(x => x.code == selectedDistrictCode)?.name || ''">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Phường / Xã</label>
                    <select name="ward_name" x-model="selectedWardCode" :disabled="!selectedDistrictCode" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-white disabled:bg-gray-50">
                        <option value="">Chọn Phường / Xã</option>
                        <template x-for="w in wards" :key="w.code">
                            <option :value="w.code" x-text="w.name"></option>
                        </template>
                    </select>
                    <input type="hidden" name="ward" :value="wards.find(x => x.code == selectedWardCode)?.name || ''">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Địa chỉ chi tiết (Số nhà, tên đường...)</label>
                    <input type="text" name="address_detail" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" name="is_default" value="1" id="is_default_check" class="rounded text-brand-600 focus:ring-brand-500">
                    <label for="is_default_check" class="text-sm text-gray-600 cursor-pointer">Đặt làm địa chỉ mặc định</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-50">
                <button type="button" @click="open = false" class="px-6 py-2.5 rounded-xl font-bold text-gray-500 hover:bg-gray-50 transition">Huỷ</button>
                <button type="submit" class="px-8 py-2.5 rounded-xl bg-brand-600 text-white font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">Thêm mới</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('addressFetcher', () => ({
            provinces: [],
            districts: [],
            wards: [],
            selectedProvinceCode: '',
            selectedDistrictCode: '',
            selectedWardCode: '',

            init() {
                this.fetchProvinces();
            },

            async fetchProvinces() {
                try {
                    const res = await fetch('https://provinces.open-api.vn/api/p/');
                    this.provinces = await res.json();
                } catch (e) { console.error('Error fetching provinces', e); }
            },

            async onProvinceChange() {
                this.districts = [];
                this.wards = [];
                this.selectedDistrictCode = '';
                this.selectedWardCode = '';
                if (!this.selectedProvinceCode) return;
                try {
                    const res = await fetch(`https://provinces.open-api.vn/api/p/${this.selectedProvinceCode}?depth=2`);
                    const data = await res.json();
                    this.districts = data.districts;
                } catch (e) { console.error('Error fetching districts', e); }
            },

            async onDistrictChange() {
                this.wards = [];
                this.selectedWardCode = '';
                if (!this.selectedDistrictCode) return;
                try {
                    const res = await fetch(`https://provinces.open-api.vn/api/d/${this.selectedDistrictCode}?depth=2`);
                    const data = await res.json();
                    this.wards = data.wards;
                } catch (e) { console.error('Error fetching wards', e); }
            }
        }));
    });
</script>
@endpush
@endsection
