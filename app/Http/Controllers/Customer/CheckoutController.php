<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use App\Models\UserAddress;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $orderService;
    protected $paymentService;

    public function __construct(CartService $cartService, OrderService $orderService, \App\Services\PaymentService $paymentService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Hiển thị trang checkout.
     */
    public function index()
    {
        $cart = $this->cartService->getCartData();
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $addresses = auth()->check() ? auth()->user()->addresses : collect();
        $defaultAddress = $addresses->where('is_default', true)->first() ?? $addresses->first();

        return view('customer.checkout.index', compact('cart', 'addresses', 'defaultAddress'));
    }

    /**
     * Xử lý đặt hàng.
     */
    public function process(Request $request)
    {
        $request->validate([
            'shipping_name'    => 'required|string|max:255',
            'shipping_phone'   => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note'             => 'nullable|string|max:1000',
            'address_id'       => 'nullable|exists:user_addresses,id',
            'payment_method'   => 'required|in:COD,MOMO,VNPAY',
        ]);

        try {
            $data = $request->all();
            
            // Logic tính toán discount từ coupon sẽ được thêm ở 3.4 sau
            $data['discount_amount'] = 0;
            $data['shipping_fee'] = 0; // Tạm thời miễn phí vận chuyển

            $order = $this->orderService->createFromCart($data);

            // Xử lý theo phương thức thanh toán
            if ($order->payment_method === 'MOMO') {
                return redirect($this->paymentService->createMomoPayment($order));
            }
            
            if ($order->payment_method === 'VNPAY') {
                return redirect($this->paymentService->createVnpayPayment($order));
            }

            // Nếu là COD, gửi mail và báo thành công luôn
            $this->sendOrderEmail($order);

            return redirect()->route('checkout.success', $order->id)->with('success', 'Đặt hàng thành công! Cảm ơn bạn đã tin tưởng PhoneStore.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }



    /**
     * Trang demo gateway (dùng khi chưa có credentials thật)
     */
    public function demoGateway(Request $request)
    {
        $order = \App\Models\Order::findOrFail($request->order_id);
        $method = $request->method ?? 'VNPAY';
        return view('customer.checkout.demo_gateway', compact('order', 'method'));
    }

    /**
     * Demo callback (xử lý kết quả từ trang demo)
     */
    public function demoCallback(Request $request)
    {
        $orderId = $request->order_id;
        $result  = $request->result;  // 'success' | 'cancel'
        $method  = strtoupper($request->method ?? 'VNPAY');

        if ($result === 'success') {
            $order = \App\Models\Order::findOrFail($orderId);

            if ($order->payment_status !== 'PAID') {
                if ($this->orderService->confirmOnlinePayment($order)) {
                    $this->sendOrderEmail($order);
                }

                \App\Models\Payment::create([
                    'order_id'         => $order->id,
                    'method'           => $method,
                    'transaction_id'   => 'DEMO_' . strtoupper(uniqid()),
                    'amount'           => $order->total_price,
                    'status'           => 'SUCCESS',
                    'gateway_response' => json_encode(['demo' => true, 'method' => $method]),
                    'paid_at'          => now(),
                ]);
            }

            $this->sendOrderEmail($order);

            return redirect()->route('checkout.success', $order->id)
                ->with('success', "Thanh toán {$method} thành công!");
        }

        return redirect()->route('checkout.index')
            ->with('error', "Thanh toán {$method} bị hủy.");
    }

    /**
     * Callback sau khi thanh toán MoMo.
     */
    public function momoCallback(Request $request)
    {
        $inputData = $request->all();
        $result = $this->paymentService->verifyMomoPayment($inputData);

        if ($result['success']) {
            $order = \App\Models\Order::find($result['order_id']);
            if ($this->orderService->confirmOnlinePayment($order)) {
                $this->sendOrderEmail($order);
            }
            return redirect()->route('checkout.success', $order->id)->with('success', 'Thanh toán MoMo thành công!');
        }

        return redirect()->route('checkout.index')->with('error', $result['message'] ?? 'Thanh toán MoMo thất bại hoặc bị hủy.');
    }



    /**
     * Callback sau khi thanh toán VNPAY.
     */
    public function vnpayCallback(Request $request)
    {
        $inputData = $request->all();
        $result = $this->paymentService->verifyVnpayPayment($inputData);

        if ($result['success']) {
            $order = \App\Models\Order::find($result['order_id']);
            if ($this->orderService->confirmOnlinePayment($order)) {
                $this->sendOrderEmail($order);
            }
            return redirect()->route('checkout.success', $order->id)->with('success', 'Thanh toán VNPAY thành công!');
        }

        return redirect()->route('checkout.index')->with('error', $result['message'] ?? 'Thanh toán VNPAY thất bại hoặc bị hủy.');
    }

    /**
     * Gửi mail xác nhận đơn hàng.
     */
    protected function sendOrderEmail($order)
    {
        try {
            \Illuminate\Support\Facades\Mail::to($order->user->email)
                ->send(new \App\Mail\OrderConfirmation($order));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Mail error: " . $e->getMessage());
        }
    }

    /**
     * Trang đặt hàng thành công.
     */
    public function success($orderId)
    {
        $order = \App\Models\Order::with('items.variant.product')->findOrFail($orderId);
        
        // Bảo mật: chỉ cho phép người mua xem trang thành công (nếu đã login)
        if (auth()->check() && $order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('customer.checkout.success', compact('order'));
    }

    /**
     * Kiểm tra mã giảm giá (AJAX).
     */
    public function checkCoupon(Request $request)
    {
        $code = $request->input('code');
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn.']);
        }

        // Kiểm tra giới hạn sử dụng (nếu có)
        // ...

        return response()->json([
            'success' => true,
            'coupon'  => $coupon,
            'message' => 'Áp dụng mã giảm giá thành công!'
        ]);
    }
}
