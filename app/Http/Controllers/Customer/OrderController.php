<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\UserSafeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $order = Order::with([
                'items.variant.product',
                'items.variant.images',
                'statusHistories.changedBy',
                'address'
            ])
            ->findOrFail($id);

        $this->authorize('view', $order);

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
            $order = Order::findOrFail($id);
            $this->authorize('cancel', $order);

            $this->orderService->cancelOrder($id, $request->reason, auth()->id(), auth()->id());

            return back()->with('success', 'Đã huỷ đơn hàng thành công.');
        } catch (\Exception $e) {
            Log::warning('Customer order cancellation failed', ['order_id' => $id, 'exception' => $e]);

            return back()->with('error', UserSafeMessage::from($e, 'Không thể huỷ đơn hàng lúc này.'));
        }
    }
}
