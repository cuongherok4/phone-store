<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng với lọc.
     */
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }

        $orders = $query->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.variant.product', 'items.variant.images', 'statusHistories.changedBy'])
            ->findOrFail($id);
            
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        
        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) return back();

        DB::transaction(function() use ($order, $oldStatus, $newStatus, $request) {
            $order->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'changed_by' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note'       => $request->note ?? "Chuyển trạng thái từ $oldStatus sang $newStatus",
                'created_at' => now(),
            ]);
            
            // Gửi thông báo cho user
            $service = app(\App\Services\NotificationService::class);
            $service->send(
                $order->user_id,
                'order',
                'Cập nhật đơn hàng #' . $order->id,
                "Đơn hàng của bạn đã được chuyển sang trạng thái: $newStatus",
                ['order_id' => $order->id]
            );

            // Gửi email cập nhật trạng thái
            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email)
                    ->send(new \App\Mail\OrderStatusChanged($order, $newStatus));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Mail error: " . $e->getMessage());
            }
        });

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    /**
     * Huỷ đơn hàng.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        
        $order = Order::findOrFail($id);
        
        if (in_array($order->status, ['COMPLETED', 'CANCELLED'])) {
            return back()->with('error', 'Không thể huỷ đơn hàng ở trạng thái này.');
        }

        DB::transaction(function() use ($order, $request) {
            $order->update(['status' => 'CANCELLED']);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'changed_by' => auth()->id(),
                'old_status' => $order->status,
                'new_status' => 'CANCELLED',
                'note'       => 'Admin huỷ đơn: ' . $request->reason,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Đã huỷ đơn hàng.');
    }

    /**
     * In hoá đơn PDF.
     */
    public function invoice($id)
    {
        $order = Order::with(['items', 'user', 'address'])->findOrFail($id);
        
        $pdf = Pdf::loadView('admin.orders.invoice', compact('order'));
        
        return $pdf->download("hoadon-{$order->id}.pdf");
    }
}
