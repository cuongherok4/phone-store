<?php

namespace Tests\Unit\Services;

use App\Services\Admin\AdminDashboardCacheService;
use App\Services\Admin\StatisticsService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class AdminDashboardCacheServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_dashboard_summary_is_cached_until_flushed(): void
    {
        $statistics = Mockery::mock(StatisticsService::class);
        $statistics
            ->shouldReceive('getSummary')
            ->once()
            ->andReturn(['total_orders' => 10]);

        $cache = new AdminDashboardCacheService($statistics);

        $this->assertSame(['total_orders' => 10], $cache->summary());
        $this->assertSame(['total_orders' => 10], $cache->summary());
    }

    public function test_dashboard_cache_can_be_flushed(): void
    {
        $statistics = Mockery::mock(StatisticsService::class);
        $statistics
            ->shouldReceive('getRevenueChartData')
            ->twice()
            ->andReturn(
                ['labels' => ['01/07'], 'values' => [100]],
                ['labels' => ['02/07'], 'values' => [200]]
            );

        $cache = new AdminDashboardCacheService($statistics);

        $this->assertSame(['labels' => ['01/07'], 'values' => [100]], $cache->revenueChart());

        $cache->flush();

        $this->assertSame(['labels' => ['02/07'], 'values' => [200]], $cache->revenueChart());
    }
}
