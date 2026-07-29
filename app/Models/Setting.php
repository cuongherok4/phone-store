<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    private const CACHE_KEY = 'settings:all';

    protected $fillable = ['key', 'value', 'group', 'description'];

    public static function get($key, $default = null)
    {
        return self::allCached()->get($key, $default);
    }

    public static function set($key, $value, $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        self::flushCache();

        return $setting;
    }

    public static function allCached(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            return self::query()->pluck('value', 'key');
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }
}
