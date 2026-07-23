<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Models\UserAddress;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $orderService;
    protected $paymentService;
    protected $inventoryService;
    protected $couponService;

    public function __construct(CartService $cartService, OrderService $orderService, \App\Services\PaymentService $paymentService, InventoryService $inventoryService, CouponService $couponService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
        $this->couponService = $couponService;
    }

    /**
     * Hiển thị trang checkout.
     */
    public function index(Request $request)
    {
        $cart = $this->cartService->getCartData();

        // Xử lý luồng Mua ngay (Direct Buy)
        if ($request->has('variant_id')) {
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity'   => 'nullable|integer|min:1|max:99',
            ]);

            $variant = \App\Models\ProductVariant::with(['product', 'images', 'variantAttributes.attributeValue'])->findOrFail($request->variant_id);
            $qty = (int) $request->get('quantity', 1);
            $stock = $this->inventoryService->getStock($variant->id);

            if ($stock < $qty) {
                return redirect()
                    ->route('customer.products.show', $variant->product->slug)
                    ->with('error', "Sản phẩm \"{$variant->product->name}\" hiện chỉ còn {$stock} sản phẩm.");
            }
            
            // Tạo một Cart "ảo" cho view
            $mockItem = new \App\Models\CartItem([
                'variant_id' => $variant->id,
                'quantity' => $qty,
                'is_selected' => true, // QUAN TRỌNG: Phải set true để accessor total tính toán được
            ]);
            $mockItem->setRelation('variant', $variant);
            
            // Ép dữ liệu vào $cart để các accessor (total, selectedItems) dùng dữ liệu ảo
            $cart->setRelation('items', collect([$mockItem]));
            $cart->setRelation('selectedItems', collect([$mockItem]));
            
            // Đảm bảo total lấy giá trị đúng
            $cart->total = $variant->price * $qty;
        }

        if ($cart->selectedItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn sản phẩm bạn muốn thanh toán.');
        }

        $addresses = auth()->check() ? auth()->user()->addresses : collect();
        $defaultAddress = $addresses->where('is_default', true)->first() ?? $addresses->first();

        // Lấy danh sách mã giảm giá khả dụng
        $coupons = Coupon::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function($q) {
                $q->whereNull('max_uses')->orWhereRaw('used_count < max_uses');
            })
            ->orderBy('expires_at', 'asc')
            ->get();

        return view('customer.checkout.index', compact('cart', 'addresses', 'defaultAddress', 'coupons'));
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
            'payment_method'   => 'required|in:COD,VNPAY',
            'variant_id'        => 'nullable|exists:product_variants,id',
            'quantity'          => 'nullable|integer|min:1|max:99',
            'coupon_code'       => 'nullable|string|max:50',
        ]);

        try {
            $data = $request->all();
            $data['shipping_fee'] = 0; // Tạm thời miễn phí vận chuyển

            $order = $this->orderService->createFromCart($data);

            // Xử lý theo phương thức thanh toán
            
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

            try {
                $isFirstConfirmation = $this->orderService->confirmOnlinePayment($order);

                \App\Models\Payment::updateOrCreate(
                    [
                        'method' => $method,
                        'transaction_id' => 'DEMO_' . $method . '_' . $order->id,
                    ],
                    [
                        'order_id'         => $order->id,
                        'amount'           => $order->total_price,
                        'status'           => 'SUCCESS',
                        'gateway_response' => json_encode(['demo' => true, 'method' => $method]),
                        'paid_at'          => now(),
                    ]
                );

                if ($isFirstConfirmation) {
                    $this->sendOrderEmail($order);
                }
            } catch (\Exception $e) {
                return redirect()->route('checkout.index')->with('error', $e->getMessage());
            }

            return redirect()->route('checkout.success', $order->id)
                ->with('success', "Thanh toán {$method} thành công!");
        }

        return redirect()->route('checkout.index')
            ->with('error', "Thanh toán {$method} bị hủy.");
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

            if (! $order) {
                return redirect()->route('checkout.index')->with('error', 'Không tìm thấy đơn hàng thanh toán.');
            }

            try {
                if ($this->orderService->confirmOnlinePayment($order)) {
                    $this->sendOrderEmail($order);
                }

                return redirect()->route('checkout.success', $order->id)->with('success', 'Thanh toán VNPAY thành công!');
            } catch (\Exception $e) {
                return redirect()->route('checkout.index')->with('error', $e->getMessage());
            }
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

        $this->authorize('view', $order);

        return view('customer.checkout.success', compact('order'));
    }

    /**
     * Kiểm tra mã giảm giá (AJAX).
     */
    public function checkCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:99',
        ]);

        try {
            $subtotal = $this->getCheckoutSubtotal($request);
            $result = $this->couponService->validate($validated['code'], auth()->id(), $subtotal);

            return response()->json([
                'success' => true,
                'coupon'  => [
                    'id' => $result['coupon']->id,
                    'code' => $result['coupon']->code,
                    'discount_type' => $result['coupon']->discount_type,
                    'discount_value' => $result['coupon']->discount_value,
                ],
                'discount_amount' => $result['discount_amount'],
                'message' => 'Áp dụng mã giảm giá thành công!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function getCheckoutSubtotal(Request $request): float
    {
        if ($request->filled('variant_id')) {
            $variant = \App\Models\ProductVariant::findOrFail($request->input('variant_id'));
            return $variant->price * (int) $request->input('quantity', 1);
        }

        return $this->cartService->getCartData()->total;
    }
}
