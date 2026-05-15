<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\OrdersExport;
use App\Exports\InventoryExport;
use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo.
     */
    public function index()
    {
        // Thống kê sơ bộ
        $stats = [
            'total_orders'    => Order::count(),
            'total_revenue'   => Order::where('status', '!=', 'CANCELLED')->sum('total_price'),
            'orders_today'    => Order::whereDate('created_at', now())->count(),
            'revenue_today'   => Order::whereDate('created_at', now())->where('status', '!=', 'CANCELLED')->sum('total_price'),
        ];

        return view('admin.reports.index', compact('stats'));
    }

    /**
     * Xuất Excel đơn hàng.
     */
    public function exportOrders(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $status    = $request->status;

        $fileName = 'danh-sach-don-hang-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new OrdersExport($startDate, $endDate, $status), $fileName);
    }

    /**
     * Xuất Excel tồn kho.
     */
    public function exportInventory()
    {
        $fileName = 'bao-cao-ton-kho-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new InventoryExport, $fileName);
    }
}
