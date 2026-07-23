<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class HomepageCacheService
{
    private const TTL_SECONDS = 600;
    private const KEYS = [
        'new_products' => 'homepage:new_products:v1',
        'featured_products' => 'homepage:featured_products:v1',
        'featured_brands' => 'homepage:featured_brands:v1',
        'main_banners' => 'homepage:main_banners:v1',
        'secondary_banners' => 'homepage:secondary_banners:v1',
    ];

    public function newProducts()
    {
        return Cache::remember(self::KEYS['new_products'], self::TTL_SECONDS, function () {
            return $this->activeProductQuery()
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
        });
    }

    public function featuredProducts()
    {
        return Cache::remember(self::KEYS['featured_products'], self::TTL_SECONDS, function () {
            return $this->activeProductQuery()
                ->orderByDesc('avg_rating')
                ->orderByDesc('review_count')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        });
    }

    public function featuredBrands()
    {
        return Cache::remember(self::KEYS['featured_brands'], self::TTL_SECONDS, function () {
            return Brand::where('is_active', true)
                ->orderBy('name')
                ->limit(6)
                ->get();
        });
    }

    public function mainBanners()
    {
        return Cache::remember(self::KEYS['main_banners'], self::TTL_SECONDS, function () {
            return Banner::active()
                ->main()
                ->orderBy('sort_order')
                ->get();
        });
    }

    public function secondaryBanners()
    {
        return Cache::remember(self::KEYS['secondary_banners'], self::TTL_SECONDS, function () {
            return Banner::active()
                ->secondary()
                ->orderBy('sort_order')
                ->limit(2)
                ->get();
        });
    }

    public function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }
    }

    private function activeProductQuery()
    {
        return Product::active()
            ->with([
                'brand',
                'variants' => function ($q) {
                    $q->active()
                        ->orderBy('price', 'asc')
                        ->with(['images' => fn ($imgQ) => $imgQ->orderBy('sort_order')]);
                },
            ]);
    }
}
