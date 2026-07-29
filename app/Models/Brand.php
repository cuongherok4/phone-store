<?php

namespace App\Models;

use App\Services\HomepageCacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Brand extends Model
{
    public const NAVIGATION_CACHE_KEY = 'navigation:active-brands';

    protected $fillable = ['name', 'slug', 'logo', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products() { return $this->hasMany(Product::class); }

    public static function flushNavigationCache(): void
    {
        Cache::forget(self::NAVIGATION_CACHE_KEY);
    }

    protected static function booted(): void
    {
        $flushCache = function () {
            self::flushNavigationCache();
            app(HomepageCacheService::class)->flush();
        };

        static::saved($flushCache);
        static::deleted($flushCache);
    }
}
