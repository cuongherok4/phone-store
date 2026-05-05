<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected $statsService;

    public function __construct(StatisticsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function index(): View
    {
        $summary = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_summary', 300, function() {
            return $this->statsService->getSummary();
        });
        
        $revenueChart = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_chart', 300, function() {
            return $this->statsService->getRevenueChartData();
        });

        $topProducts = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_top_products', 300, function() {
            return $this->statsService->getTopSellingProducts();
        });

        $orderStatus = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_status', 300, function() {
            return $this->statsService->getOrderStatusDistribution();
        });

        $lowStockVariants = $this->statsService->getLowStockVariants();
        $recentOrders = $this->statsService->getRecentOrders(); // Recent orders don't cache or cache shorter

        return view('admin.dashboard.index', compact(
            'summary', 'revenueChart', 'topProducts', 'orderStatus', 'recentOrders', 'lowStockVariants'
        ));
    }
}