<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Policies\OrderPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);

        Gate::before(function (User $user) {
            return $user->isAdmin() ? true : null;
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(6)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('coupon-check', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('layouts.app', function ($view) {
            $globalBrands = Cache::remember(Brand::NAVIGATION_CACHE_KEY, now()->addMinutes(30), function () {
                return Brand::where('is_active', true)
                    ->orderBy('name')
                    ->limit(8)
                    ->get();
            });

            $globalSettings = Setting::allCached();

            $view->with([
                'globalBrands' => $globalBrands,
                'globalSettings' => $globalSettings,
            ]);
        });
    }
}
