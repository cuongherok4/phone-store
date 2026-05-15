<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    protected $cartService;
    protected $inventoryService;

    public function __construct(CartService $cartService, InventoryService $inventoryService)
    {
        $this->cartService = $cartService;
        $this->inventoryService = $inventoryService;
    }

    public function createFromCart(array $data)
    {
        $paymentMethod = $data['payment_method'] ?? 'COD';
        $isOnlinePayment = in_array($paymentMethod, ['MOMO', 'VNPAY']);

        // LUỒNG MUA NGAY (Direct Buy - Bỏ qua giỏ hàng)
        if (isset($data['variant_id'])) {
            $variant = \App\Models\ProductVariant::findOrFail($data['variant_id']);
            $qty = $data['quantity'] ?? 1;
            $subtotal = $variant->price * $qty;

            return DB::transaction(function () use ($variant, $qty, $data, $isOnlinePayment, $subtotal) {
                // Kiểm tra tồn kho
                $stock = $this->inventoryService->getStock($variant->id);
                if ($stock < $qty) {
                    throw new Exception("Sản phẩm {$variant->product->name} hiện chỉ còn {$stock} sản phẩm.");
                }

                $order = Order::create([
                    'user_id'          => auth()->id(),
                    'address_id'       => $data['address_id'] ?? null,
                    'coupon_id'        => $data['coupon_id'] ?? null,
                    'subtotal'         => $subtotal,
                    'discount_amount'  => $data['discount_amount'] ?? 0,
                    'shipping_fee'     => $data['shipping_fee'] ?? 0,
                    'total_price'      => $subtotal + ($data['shipping_fee'] ?? 0) - ($data['discount_amount'] ?? 0),
                    'status'           => 'PENDING',
                    'payment_status'   => 'UNPAID',
                    'payment_method'   => $data['payment_method'] ?? 'COD',
                    'shipping_name'    => $data['shipping_name'],
                    'shipping_phone'   => $data['shipping_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'note'             => $data['note'] ?? null,
                ]);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'variant_id' => $variant->id,
                    'quantity'   => $qty,
                    'price'      => $variant->price,
                    'subtotal'   => $subtotal,
                ]);

                // Ghi log lịch sử
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status'   => 'PENDING',
                    'note'     => 'Đơn hàng được tạo (Mua ngay)',
                ]);

                if (!$isOnlinePayment) {
                    $this->inventoryService->reduceStock($variant->id, $qty, "Đặt hàng trực tiếp #{$order->id}");
                }

                // Xoá sản phẩm này khỏi giỏ hàng nếu có
                $cart = $this->cartService->getOrCreateCart();
                $cart->items()->where('variant_id', $variant->id)->delete();

                return $order;
            });
        }

        // LUỒNG GIỎ HÀNG THÔNG THƯỜNG
        $cart = $this->cartService->getOrCreateCart();
        $selectedItems = $cart->selectedItems;
        
        if ($selectedItems->isEmpty()) {
            throw new Exception("Vui lòng chọn ít nhất một sản phẩm để thanh toán.");
        }

        return DB::transaction(function () use ($cart, $selectedItems, $data, $isOnlinePayment) {
            // 1. Tạo bản ghi Order
            $order = Order::create([
                'user_id'          => auth()->id(),
                'address_id'       => $data['address_id'] ?? null,
                'coupon_id'        => $data['coupon_id'] ?? null,
                'subtotal'         => $cart->total,
                'discount_amount'  => $data['discount_amount'] ?? 0,
                'shipping_fee'     => $data['shipping_fee'] ?? 0,
                'total_price'      => $cart->total + ($data['shipping_fee'] ?? 0) - ($data['discount_amount'] ?? 0),
                'status'           => 'PENDING',
                'payment_status'   => 'UNPAID',
                'payment_method'   => $data['payment_method'] ?? 'COD',
                'shipping_name'    => $data['shipping_name'],
                'shipping_phone'   => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'note'             => $data['note'] ?? null,
            ]);

            // 2. Chuyển Cart Items sang Order Items
            foreach ($selectedItems as $cartItem) {
                // Kiểm tra tồn kho cho tất cả phương thức
                $stock = $this->inventoryService->getStock($cartItem->variant_id);
                if ($stock < $cartItem->quantity) {
                    throw new Exception("Sản phẩm \"{$cartItem->variant->product->name}\" hiện đã hết hàng hoặc không đủ số lượng.");
                }

                // Lấy tên variant (RAM/ROM/màu)
                $variantAttrs = $cartItem->variant->variantAttributes
                    ->map(fn($va) => $va->attributeValue->value ?? '')
                    ->filter()->implode(' / ');

                OrderItem::create([
                    'order_id'   => $order->id,
                    'variant_id' => $cartItem->variant_id,
                    'sku'        => $cartItem->variant->sku,
                    'name'       => $cartItem->variant->product->name . ($variantAttrs ? ' (' . $variantAttrs . ')' : ''),
                    'price'      => $cartItem->variant->price,
                    'quantity'   => $cartItem->quantity,
                ]);

                // Chỉ trừ kho ngay với COD — Online payment chờ callback
                if (!$isOnlinePayment) {
                    $this->inventoryService->deduct($cartItem->variant_id, $cartItem->quantity, $order->id);
                }
            }

            // 3. Ghi lại lịch sử trạng thái
            $this->logStatus($order->id, 'PENDING', 'Khách hàng đặt hàng thành công.');

            // 4. Xoá giỏ hàng ngay sau khi tạo đơn hàng
            $this->cartService->clearCart();

            return $order;
        });
    }

    /**
     * Xác nhận thanh toán online thành công — trừ kho và xóa giỏ hàng.
     * Gọi sau khi callback VNPAY / MoMo xác nhận Payment thành công.
     * @return bool Trả về true nếu đây là lần đầu xác nhận thành công
     */
    public function confirmOnlinePayment(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            // Sử dụng atomic update để kiểm tra và đánh dấu PAID cùng lúc
            // Tránh race condition nếu IPN và Redirect callback xảy ra đồng thời
            $affected = DB::table('orders')
                ->where('id', $order->id)
                ->where('payment_status', '!=', 'PAID')
                ->update([
                    'payment_status' => 'PAID',
                    'status'         => 'CONFIRMED',
                    'updated_at'     => now(),
                ]);

            if ($affected > 0) {
                // 1. Trừ tồn kho
                foreach ($order->items as $item) {
                    $this->inventoryService->deduct($item->variant_id, $item->quantity, $order->id);
                }

                $this->logStatus($order->id, 'CONFIRMED', 'Thanh toán online thành công. Đơn hàng đã được xác nhận.');

                // 2. Xoá giỏ hàng của user
                $this->cartService->clearCart();
                
                // Refresh model data
                $order->refresh();
                
                return true;
            }
            
            return false;
        });
    }

    /**
     * Huỷ đơn hàng.
     */
    public function cancelOrder(int $orderId, string $reason, $userId = null)
    {
        $order = Order::findOrFail($orderId);

        if (!$order->canBeCancelled()) {
            throw new Exception("Đơn hàng này không thể huỷ ở trạng thái hiện tại.");
        }

        DB::transaction(function () use ($order, $reason, $userId) {
            $order->update([
                'status' => 'CANCELLED',
                'cancelled_reason' => $reason
            ]);

            // Hoàn lại tồn kho nếu đơn hàng đã trừ kho trước đó
            // Tồn kho được trừ khi: COD (trừ ngay) hoặc Online Payment (đã thanh toán PAID)
            $inventoryDeducted = ($order->payment_method === 'COD') || ($order->payment_status === 'PAID');

            if ($inventoryDeducted) {
                foreach ($order->items as $item) {
                    $this->inventoryService->restore($item->variant_id, $item->quantity, $order->id);
                }
            }

            $this->logStatus($order->id, 'CANCELLED', "Huỷ đơn hàng. Lý do: {$reason}", $userId);
        });

        return $order;
    }

    /**
     * Cập nhật trạng thái đơn hàng (Admin).
     */
    public function updateStatus(int $orderId, string $status, string $note = null, $userId = null)
    {
        $order = Order::findOrFail($orderId);
        
        DB::transaction(function () use ($order, $status, $note, $userId) {
            $updateData = ['status' => $status];
            
            // Nếu đơn hàng hoàn thành, tự động chuyển sang Đã thanh toán
            if ($status === 'COMPLETED') {
                $updateData['payment_status'] = 'PAID';
            }

            $order->update($updateData);
            $this->logStatus($order->id, $status, $note, $userId);
        });

        return $order;
    }

    /**
     * Ghi lịch sử trạng thái.
     */
    protected function logStatus(int $orderId, string $status, string $note = null, $userId = null)
    {
        $order = Order::find($orderId);
        $oldStatus = $order ? $order->status : null;

        OrderStatusHistory::create([
            'order_id'   => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'note'       => $note,
            'changed_by' => $userId ?? auth()->id(),
            'created_at' => now(),
        ]);
    }
}
