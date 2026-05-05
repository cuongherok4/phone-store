<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Danh sách đơn hàng của khách hàng.
     */
    public function index()
    {
        $orders = auth()->user()->orders()
            ->with(['items.variant.product', 'items.variant.images'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng.
     */
    public function show($id)
    {
        $order = auth()->user()->orders()
            ->with([
                'items.variant.product', 
                'items.variant.images',
                'statusHistories.changedBy',
                'address'
            ])
            ->findOrFail($id);

        return view('customer.orders.show', compact('order'));
    }

    /**
     * Huỷ đơn hàng.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            // Kiểm tra quyền sở hữu
            $order = auth()->user()->orders()->findOrFail($id);
            
            $this->orderService->cancelOrder($id, $request->reason, auth()->id());

            return back()->with('success', 'Đã huỷ đơn hàng thành công.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
