<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardCacheService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private AdminDashboardCacheService $dashboardCache)
    {
    }

    public function index(): View
    {
        $summary = $this->dashboardCache->summary();
        $revenueChart = $this->dashboardCache->revenueChart();
        $topProducts = $this->dashboardCache->topProducts();
        $orderStatus = $this->dashboardCache->orderStatus();
        $lowStockVariants = $this->dashboardCache->lowStockVariants();
        $recentOrders = $this->dashboardCache->recentOrders();

        return view('admin.dashboard.index', compact(
            'summary', 'revenueChart', 'topProducts', 'orderStatus', 'recentOrders', 'lowStockVariants'
        ));
    }
}
