<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsService
{
    /**
     * Get summary counts and totals.
     */
    public function getSummary()
    {
        $now = Carbon::now();
        
        return [
            'total_revenue' => Order::where('status', 'COMPLETED')->orWhere('payment_status', 'PAID')->sum('total_price'),
            'monthly_revenue' => Order::where(function($q) {
                    $q->where('status', 'COMPLETED')->orWhere('payment_status', 'PAID');
                })
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('total_price'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'PENDING')->count(),
            'total_stock_value' => DB::table('product_variants')
                ->join('inventory', 'inventory.variant_id', '=', 'product_variants.id')
                ->select(DB::raw('SUM(product_variants.price * inventory.quantity)'))
                ->value('sum'),
            'low_stock_variants' => $this->getLowStockVariants()->count(),
            'total_vnpay' => Order::where('payment_method', 'VNPAY')
                ->where(function($q) {
                    $q->where('status', 'COMPLETED')->orWhere('payment_status', 'PAID');
                })
                ->sum('total_price'),
            'total_momo' => Order::where('payment_method', 'MOMO')
                ->where(function($q) {
                    $q->where('status', 'COMPLETED')->orWhere('payment_status', 'PAID');
                })
                ->sum('total_price'),
        ];
    }

    /**
     * Get list of low stock variants with their current quantity.
     */
    public function getLowStockVariants(int $limit = 5)
    {
        return ProductVariant::with(['product', 'inventory'])
            ->where(function($query) {
                $query->selectRaw('COALESCE(SUM(quantity), 0)')
                      ->from('inventory')
                      ->whereColumn('inventory.variant_id', 'product_variants.id');
            }, '<', 5)
            ->get()
            ->map(function($variant) {
                $variant->total_quantity = $variant->inventory->sum('quantity');
                return $variant;
            })
            ->take($limit);
    }

    /**
     * Get revenue data for Chart.js (last 30 days).
     */
    public function getRevenueChartData()
    {
        $data = Order::where(function($q) {
                $q->where('status', 'COMPLETED')->orWhere('payment_status', 'PAID');
            })
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }

    /**
     * Get top selling products.
     */
    public function getTopSellingProducts(int $limit = 5)
    {
        return OrderItem::select('name', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    /**
     * Get order status distribution.
     */
    public function getOrderStatusDistribution()
    {
        return Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * Get recent orders.
     */
    public function getRecentOrders(int $limit = 5)
    {
        return Order::with('user')->latest()->limit($limit)->get();
    }
}
