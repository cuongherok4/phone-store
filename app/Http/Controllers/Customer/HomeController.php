<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Banner;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy 8 sản phẩm mới nhất — eager load đầy đủ tránh N+1
        $newProducts = Product::where('status', 1)
            ->whereNull('deleted_at')
            ->with([
                'brand',
                'variants' => function ($q) {
                    $q->where('is_active', true)
                      ->whereNull('deleted_at')
                      ->orderBy('price', 'asc')
                      ->with(['images' => fn ($imgQ) => $imgQ->orderBy('sort_order')]);
                },
            ])
            ->withAvg(['reviews as avg_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews as review_count' => fn ($q) => $q->where('is_approved', true)])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Lấy 8 sản phẩm nổi bật (random vì chưa có bảng tracking view/sales)
        $featuredProducts = Product::where('status', 1)
            ->whereNull('deleted_at')
            ->with([
                'brand',
                'variants' => function ($q) {
                    $q->where('is_active', true)
                      ->whereNull('deleted_at')
                      ->orderBy('price', 'asc')
                      ->with(['images' => fn ($imgQ) => $imgQ->orderBy('sort_order')]);
                },
            ])
            ->withAvg(['reviews as avg_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews as review_count' => fn ($q) => $q->where('is_approved', true)])
            ->inRandomOrder()
            ->limit(8)
            ->get();


        // Thương hiệu nổi bật
        $featuredBrands = Brand::where('is_active', true)->limit(6)->get();

        // Lấy Banners
        $mainBanners = Banner::active()->main()->orderBy('sort_order')->get();
        $secondaryBanners = Banner::active()->secondary()->orderBy('sort_order')->limit(2)->get();

        return view('customer.home.index', compact(
            'newProducts',
            'featuredProducts',
            'featuredBrands',
            'mainBanners',
            'secondaryBanners'
        ));
    }
}