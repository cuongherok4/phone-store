<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Cache;

class AdminDashboardCacheService
{
    private const TTL_SECONDS = 300;
    private const SHORT_TTL_SECONDS = 60;
    private const KEYS = [
        'summary' => 'admin_dashboard:summary:v1',
        'revenue_chart' => 'admin_dashboard:revenue_chart:v1',
        'top_products' => 'admin_dashboard:top_products:v1',
        'order_status' => 'admin_dashboard:order_status:v1',
        'low_stock_variants' => 'admin_dashboard:low_stock_variants:v1',
        'recent_orders' => 'admin_dashboard:recent_orders:v1',
    ];

    public function __construct(private StatisticsService $statistics)
    {
    }

    public function summary(): array
    {
        return Cache::remember(self::KEYS['summary'], self::TTL_SECONDS, fn () => $this->statistics->getSummary());
    }

    public function revenueChart(): array
    {
        return Cache::remember(self::KEYS['revenue_chart'], self::TTL_SECONDS, fn () => $this->statistics->getRevenueChartData());
    }

    public function topProducts()
    {
        return Cache::remember(self::KEYS['top_products'], self::TTL_SECONDS, fn () => $this->statistics->getTopSellingProducts());
    }

    public function orderStatus()
    {
        return Cache::remember(self::KEYS['order_status'], self::TTL_SECONDS, fn () => $this->statistics->getOrderStatusDistribution());
    }

    public function lowStockVariants()
    {
        return Cache::remember(self::KEYS['low_stock_variants'], self::SHORT_TTL_SECONDS, fn () => $this->statistics->getLowStockVariants());
    }

    public function recentOrders()
    {
        return Cache::remember(self::KEYS['recent_orders'], self::SHORT_TTL_SECONDS, fn () => $this->statistics->getRecentOrders());
    }

    public function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }
    }
}
