@extends('layouts.app')

@section('title', $pageTitle . ' - Phone Store')

@section('content')
<div class="bg-gray-50 py-8">
    <div class="tail-container">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-600"><i class="fas fa-home"></i> Trang chủ</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">{{ $pageTitle }}</span>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 text-green-700 text-sm">
                <i class="fas fa-check-circle text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-700 text-sm">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8">

            {{-- SIDEBAR FILTER --}}
            <aside class="w-full lg:w-64 flex-shrink-0">
                <form action="{{ route('customer.products.index') }}" method="GET" id="filterForm"
                      class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">

                    {{-- Preserve search query --}}
                    @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif

                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-lg text-gray-900">Bộ lọc</h3>
                        @if(request()->hasAny(['brand', 'min_price', 'max_price']))
                            <a href="{{ route('customer.products.index', request()->only(['q', 'sort'])) }}"
                               class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-1">
                                <i class="fas fa-times-circle"></i> Xóa lọc
                            </a>
                        @endif
                    </div>


                    <hr class="border-gray-100 my-5">

                    {{-- Thương hiệu --}}
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-tag text-brand-500 text-xs"></i> Thương hiệu
                        </h4>
                        @php $selectedBrands = array_filter(explode(',', request('brand', ''))); @endphp
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($allBrands as $brand)
                                <label class="flex items-center justify-center gap-1 cursor-pointer border rounded-lg p-2 text-center transition
                                              {{ in_array($brand->slug, $selectedBrands) ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 text-gray-700 hover:border-brand-300' }}">
                                    <input type="checkbox" name="brand_array[]" value="{{ $brand->slug }}"
                                           {{ in_array($brand->slug, $selectedBrands) ? 'checked' : '' }}
                                           class="hidden brand-checkbox">
                                    <span class="text-xs font-medium leading-tight">{{ $brand->name }}</span>
                                </label>
                            @endforeach
                            <input type="hidden" name="brand" id="brandInput" value="{{ request('brand') }}">
                        </div>
                    </div>

                    <hr class="border-gray-100 my-5">

                    {{-- Khoảng giá --}}
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-dollar-sign text-brand-500 text-xs"></i> Khoảng giá
                        </h4>
                        {{-- Quick price ranges --}}
                        <div class="space-y-1.5 mb-3">
                            @php
                                $priceRanges = [
                                    ['label' => 'Dưới 3 triệu',       'min' => 0,        'max' => 3000000],
                                    ['label' => '3 - 7 triệu',        'min' => 3000000,  'max' => 7000000],
                                    ['label' => '7 - 15 triệu',       'min' => 7000000,  'max' => 15000000],
                                    ['label' => 'Trên 15 triệu',      'min' => 15000000, 'max' => 999999999],
                                ];
                            @endphp
                            @foreach($priceRanges as $range)
                                <a href="{{ route('customer.products.index', array_merge(request()->only(['q', 'sort', 'brand']), ['min_price' => $range['min'], 'max_price' => $range['max']])) }}"
                                   class="block text-xs px-3 py-2 rounded-lg transition
                                          {{ request('min_price') == $range['min'] && request('max_price') == $range['max']
                                             ? 'bg-brand-600 text-white font-medium'
                                             : 'bg-gray-50 text-gray-600 hover:bg-brand-50 hover:text-brand-600' }}">
                                    {{ $range['label'] }}
                                </a>
                            @endforeach
                        </div>
                        {{-- Custom range --}}
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}"
                                   placeholder="Từ..."
                                   class="w-full text-xs border-gray-300 rounded-lg focus:border-brand-500 focus:ring focus:ring-brand-200 p-2 border">
                            <span class="text-gray-400 flex-shrink-0">—</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}"
                                   placeholder="Đến..."
                                   class="w-full text-xs border-gray-300 rounded-lg focus:border-brand-500 focus:ring focus:ring-brand-200 p-2 border">
                        </div>
                        <button type="submit"
                                class="w-full mt-2 bg-gray-900 text-white hover:bg-brand-600 font-medium py-2 rounded-lg transition text-xs">
                            Áp dụng
                        </button>
                    </div>

                </form>
            </aside>

            {{-- MAIN CONTENT --}}
            <div class="flex-1 min-w-0">

                {{-- Toolbar --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Tìm thấy <b>{{ $products->total() }}</b> sản phẩm
                            @if($products->total() > 0 && $products->lastPage() > 1)
                                · Trang {{ $products->currentPage() }}/{{ $products->lastPage() }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span class="text-sm text-gray-500 hidden sm:block">Sắp xếp:</span>
                        <select onchange="updateSort(this.value)"
                                class="text-sm border-gray-300 rounded-xl focus:border-brand-500 focus:ring focus:ring-brand-200 cursor-pointer pr-8">
                            <option value="newest"     {{ request('sort', 'newest') === 'newest'     ? 'selected' : '' }}>Mới nhất</option>
                            <option value="price_asc"  {{ request('sort') === 'price_asc'            ? 'selected' : '' }}>Giá: Thấp → Cao</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc'           ? 'selected' : '' }}>Giá: Cao → Thấp</option>
                        </select>
                    </div>
                </div>

                {{-- Active Filters Tags --}}
                @if(request()->hasAny(['category', 'brand', 'min_price', 'max_price', 'q']))
                    <div class="flex flex-wrap gap-2 mb-4">
                        @if(request('q'))
                            <span class="inline-flex items-center gap-1.5 text-xs bg-brand-100 text-brand-700 px-3 py-1.5 rounded-full font-medium">
                                <i class="fas fa-search text-[10px]"></i> "{{ request('q') }}"
                                <a href="{{ route('customer.products.index', request()->except('q')) }}" class="ml-1 hover:text-brand-900"><i class="fas fa-times"></i></a>
                            </span>
                        @endif
                        @if(request('brand'))
                            <span class="inline-flex items-center gap-1.5 text-xs bg-orange-100 text-orange-700 px-3 py-1.5 rounded-full font-medium">
                                <i class="fas fa-tag text-[10px]"></i> {{ $allBrands->whereIn('slug', explode(',', request('brand')))->pluck('name')->implode(', ') }}
                                <a href="{{ route('customer.products.index', request()->except('brand')) }}" class="ml-1 hover:text-orange-900"><i class="fas fa-times"></i></a>
                            </span>
                        @endif
                        @if(request('min_price') || request('max_price'))
                            <span class="inline-flex items-center gap-1.5 text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-full font-medium">
                                <i class="fas fa-dollar-sign text-[10px]"></i>
                                {{ request('min_price') ? number_format(request('min_price'), 0, ',', '.') . 'đ' : '0đ' }}
                                —
                                {{ request('max_price') && request('max_price') < 999999999 ? number_format(request('max_price'), 0, ',', '.') . 'đ' : '...' }}
                                <a href="{{ route('customer.products.index', request()->except(['min_price', 'max_price'])) }}" class="ml-1 hover:text-green-900"><i class="fas fa-times"></i></a>
                            </span>
                        @endif
                    </div>
                @endif

                {{-- Product Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
                    @forelse($products as $product)
                        <x-product-card :product="$product" />
                    @empty
                        <div class="col-span-full bg-white rounded-2xl p-16 text-center border border-gray-100">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-search text-3xl text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-700 mb-2">Không tìm thấy sản phẩm</h3>
                            <p class="text-gray-500 mb-6">Vui lòng thử điều chỉnh lại bộ lọc hoặc từ khóa tìm kiếm.</p>
                            <a href="{{ route('customer.products.index') }}"
                               class="inline-flex items-center gap-2 bg-brand-600 text-white font-medium px-6 py-2.5 rounded-xl hover:bg-brand-700 transition">
                                <i class="fas fa-times"></i> Xóa tất cả bộ lọc
                            </a>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($products->hasPages())
                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Checkbox thương hiệu → dồn thành string cách dấu phẩy
    document.querySelectorAll('.brand-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const selected = [...document.querySelectorAll('.brand-checkbox:checked')].map(c => c.value);
            document.getElementById('brandInput').value = selected.join(',');
            document.getElementById('filterForm').submit();
        });
    });

    // Cập nhật sort mà không làm mất filters khác
    function updateSort(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', val);
        window.location.href = url.toString();
    }
</script>
@endpush
