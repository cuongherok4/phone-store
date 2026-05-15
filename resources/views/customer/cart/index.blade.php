@extends('layouts.app')

@section('title', 'Giỏ hàng - Phone Store')

@section('content')
<div class="bg-gray-50 min-h-screen py-12" x-data="cartManager()">
    <div class="tail-container">
        
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Trang chủ</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Giỏ hàng</span>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-10 flex items-center gap-3">
            <i class="fas fa-shopping-cart text-brand-600"></i>
            Giỏ hàng của bạn
            <span class="text-lg font-normal text-gray-400" x-text="'(' + itemCount + ' sản phẩm)'"></span>
        </h1>

        <div class="flex flex-col lg:flex-row gap-8">
            
            {{-- Main Cart Items --}}
            <div class="lg:w-2/3 space-y-4">
                @if($cart->items->isEmpty())
                    <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-gray-100">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-shopping-basket text-4xl text-gray-200"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Giỏ hàng trống</h2>
                        <p class="text-gray-500 mb-8">Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
                        <a href="{{ route('customer.products.index') }}" 
                           class="inline-flex items-center gap-2 bg-brand-600 text-white px-8 py-3 rounded-full font-bold hover:bg-brand-700 transition shadow-lg shadow-brand-500/30">
                            Tiếp tục mua sắm
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                @else
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="px-6 py-4 w-10">
                                        <input type="checkbox" x-model="allSelected" @change="toggleAll()" 
                                               class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                                    </th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Sản phẩm</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Số lượng</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($cart->items as $item)
                                    <tr class="group hover:bg-gray-50/30 transition-colors {{ !$item->is_selected ? 'opacity-70 bg-gray-50/10' : '' }}" id="cart-item-{{ $item->id }}">
                                        <td class="px-6 py-6">
                                            <input type="checkbox" :checked="items[{{ $item->id }}]?.selected" @change="toggleItem({{ $item->id }})"
                                                   class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                                        </td>
                                        <td class="px-6 py-6">
                                            <div class="flex items-center gap-4">
                                                {{-- Image --}}
                                                <div class="w-20 h-20 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100 p-2">
                                                    @php
                                                        $primaryImg = $item->variant->images->where('is_primary', true)->first()?->image_url 
                                                                   ?? $item->variant->images->first()?->image_url;
                                                        $imgUrl = $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : 'https://placehold.co/400x400?text=No+Image';
                                                    @endphp
                                                    <img src="{{ $imgUrl }}" class="w-full h-full object-contain mix-blend-multiply">
                                                </div>
                                                {{-- Info --}}
                                                <div class="min-w-0">
                                                    <a href="{{ route('customer.products.show', $item->variant->product->slug) }}" 
                                                       class="block text-sm font-bold text-gray-900 hover:text-brand-600 transition mb-1 truncate">
                                                        {{ $item->variant->product->name }}
                                                    </a>
                                                    <p class="text-xs text-gray-500 mb-2">
                                                        @foreach($item->variant->variantAttributes as $va)
                                                            <span class="inline-flex items-center gap-1">
                                                                {{ $va->attribute->name }}: <span class="text-gray-700 font-medium">{{ $va->attributeValue->value }}</span>
                                                                @if(!$loop->last) <span class="mx-1 text-gray-300">|</span> @endif
                                                            </span>
                                                        @endforeach
                                                    </p>
                                                    <button @click="removeItem({{ $item->id }})" 
                                                            class="text-xs text-red-400 hover:text-red-600 font-medium flex items-center gap-1 transition">
                                                        <i class="far fa-trash-alt"></i> Xoá
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-6 text-center">
                                            <div class="inline-flex items-center border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                                                <button @click="updateQty({{ $item->id }}, -1)" 
                                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-brand-600 transition">−</button>
                                                <span class="w-10 text-center text-sm font-bold text-gray-900" id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                                <button @click="updateQty({{ $item->id }}, 1)" 
                                                        class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-brand-600 transition">+</button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-6 text-right font-bold text-gray-900">
                                            <span id="subtotal-{{ $item->id }}">{{ number_format($item->subtotal, 0, ',', '.') }}đ</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                
                {{-- Promotion --}}
                @if(!$cart->items->isEmpty())
                    <div class="bg-brand-50 rounded-2xl p-6 border border-brand-100 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-brand-600 text-xl shadow-sm">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-brand-900">Ưu đãi độc quyền</h4>
                                <p class="text-sm text-brand-700">Miễn phí vận chuyển cho đơn hàng từ 10.000.000đ</p>
                            </div>
                        </div>
                        <a href="{{ route('customer.products.index') }}" class="text-sm font-bold text-brand-600 hover:underline">Tiếp tục mua sắm <i class="fas fa-chevron-right text-[10px] ml-1"></i></a>
                    </div>
                @endif
            </div>

            {{-- Summary Sidebar --}}
            @if(!$cart->items->isEmpty())
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 uppercase tracking-tight">Tóm tắt đơn hàng</h2>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-gray-500">
                                <span>Tạm tính (<span x-text="selectedCount"></span> sản phẩm)</span>
                                <span class="font-bold text-gray-900" x-text="total"></span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Phí vận chuyển</span>
                                <span class="text-green-600 font-bold">Miễn phí</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Giảm giá</span>
                                <span class="text-red-500 font-bold">-0đ</span>
                            </div>
                            <hr class="border-gray-100">
                            <div class="flex justify-between items-end">
                                <span class="text-lg font-bold text-gray-900">Tổng cộng</span>
                                <div class="text-right">
                                    <p class="text-2xl font-black text-red-600 leading-none" x-text="total"></p>
                                    <p class="text-[10px] text-gray-400 mt-1">(Đã bao gồm VAT nếu có)</p>
                                </div>
                            </div>
                        </div>

                        <button @click="checkout"
                                :disabled="selectedCount === 0"
                                :class="selectedCount === 0 ? 'bg-gray-300 cursor-not-allowed shadow-none' : 'bg-brand-600 hover:bg-brand-700 shadow-brand-500/30'"
                                class="block w-full text-white text-center py-4 rounded-xl font-bold text-lg transition shadow-lg mb-4">
                            Mua hàng (<span x-text="selectedCount"></span>)
                        </button>
                        
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <i class="fas fa-shield-alt text-green-500"></i>
                                Thanh toán an toàn và bảo mật 100%
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <i class="fas fa-undo text-orange-500"></i>
                                Chính sách đổi trả miễn phí trong 30 ngày
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartManager', () => ({
            itemCount: {{ $cart->item_count }},
            selectedCount: {{ $cart->items->where('is_selected', true)->count() }},
            total: '{{ number_format($cart->total, 0, ',', '.') }}đ',
            allSelected: {{ $cart->items->where('is_selected', false)->count() === 0 ? 'true' : 'false' }},
            items: {
                @foreach($cart->items as $item)
                    {{ $item->id }}: { selected: {{ $item->is_selected ? 'true' : 'false' }}, subtotal: {{ $item->subtotal }} },
                @endforeach
            },
            
            async updateQty(itemId, delta) {
                const qtySpan = document.getElementById(`qty-${itemId}`);
                let currentQty = parseInt(qtySpan.innerText);
                let newQty = currentQty + delta;
                
                if (newQty < 1) {
                    if (confirm('Bạn có muốn xoá sản phẩm này khỏi giỏ hàng?')) {
                        this.removeItem(itemId);
                    }
                    return;
                }

                try {
                    const response = await fetch(`/gio-hang/cap-nhat/${itemId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ quantity: newQty })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        window.location.reload(); // Đơn giản nhất để đồng bộ toàn bộ UI
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Update failed:', error);
                }
            },

            async toggleItem(itemId) {
                const isSelected = !this.items[itemId].selected;
                try {
                    const response = await fetch(`/gio-hang/toggle/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ is_selected: isSelected })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.items[itemId].selected = isSelected;
                        this.total = data.total;
                        this.selectedCount = data.item_count;
                        this.checkAllSelected();
                        // Thêm class mờ nếu không chọn
                        const row = document.getElementById(`cart-item-${itemId}`);
                        if (!isSelected) {
                            row.classList.add('opacity-70', 'bg-gray-50/10');
                        } else {
                            row.classList.remove('opacity-70', 'bg-gray-50/10');
                        }
                    }
                } catch (error) { console.error(error); }
            },

            async toggleAll() {
                try {
                    const response = await fetch(`/gio-hang/toggle-all`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ is_selected: this.allSelected })
                    });
                    const data = await response.json();
                    if (data.success) {
                        Object.keys(this.items).forEach(id => {
                            this.items[id].selected = this.allSelected;
                            const row = document.getElementById(`cart-item-${id}`);
                            if (!this.allSelected) {
                                row.classList.add('opacity-70', 'bg-gray-50/10');
                            } else {
                                row.classList.remove('opacity-70', 'bg-gray-50/10');
                            }
                        });
                        this.total = data.total;
                        this.selectedCount = data.item_count;
                    }
                } catch (error) { console.error(error); }
            },

            checkAllSelected() {
                this.allSelected = Object.values(this.items).every(i => i.selected);
            },

            checkout() {
                if (this.selectedCount > 0) {
                    window.location.href = '{{ route("checkout.index") }}';
                }
            },

            async removeItem(itemId) {
                if (!confirm('Xác nhận xoá sản phẩm này?')) return;

                try {
                    const response = await fetch(`/gio-hang/xoa/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Remove failed:', error);
                }
            }
        }));
    });
</script>
@endpush
