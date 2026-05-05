<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Brand;
use App\Models\Setting;

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
        View::composer('layouts.app', function ($view) {
            $globalBrands = Brand::where('is_active', true)
                ->orderBy('name')
                ->limit(8)
                ->get();

            $globalSettings = Setting::all()->pluck('value', 'key');

            $view->with([
                'globalBrands' => $globalBrands,
                'globalSettings' => $globalSettings,
            ]);
        });
    }
}
