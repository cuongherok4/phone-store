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
    protected $couponService;

    public function __construct(CartService $cartService, InventoryService $inventoryService, CouponService $couponService)
    {
        $this->cartService = $cartService;
        $this->inventoryService = $inventoryService;
        $this->couponService = $couponService;
    }

    public function createFromCart(array $data)
    {
        $paymentMethod = $data['payment_method'] ?? 'COD';
        $isOnlinePayment = in_array($paymentMethod, ['MOMO', 'VNPAY']);

        // LUỒNG MUA NGAY (Direct Buy - Bỏ qua giỏ hàng)
        if (isset($data['variant_id'])) {
            $variant = \App\Models\ProductVariant::with(['product', 'variantAttributes.attributeValue'])
                ->findOrFail($data['variant_id']);
            $qty = $data['quantity'] ?? 1;
            $subtotal = $variant->price * $qty;

            return DB::transaction(function () use ($variant, $qty, $data, $isOnlinePayment, $subtotal) {
                $couponData = $this->resolveCoupon($data, $subtotal);

                $this->inventoryService->assertAvailable(
                    $variant->id,
                    $qty,
                    $variant->product->name,
                    true
                );

                $order = Order::create([
                    'user_id'          => auth()->id(),
                    'address_id'       => $data['address_id'] ?? null,
                    'coupon_id'        => $couponData['coupon']?->id,
                    'subtotal'         => $subtotal,
                    'discount_amount'  => $couponData['discount_amount'],
                    'shipping_fee'     => $data['shipping_fee'] ?? 0,
                    'total_price'      => $subtotal + ($data['shipping_fee'] ?? 0) - $couponData['discount_amount'],
                    'status'           => 'PENDING',
                    'payment_status'   => 'UNPAID',
                    'payment_method'   => $data['payment_method'] ?? 'COD',
                    'shipping_name'    => $data['shipping_name'],
                    'shipping_phone'   => $data['shipping_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'note'             => $data['note'] ?? null,
                ]);

                $variantAttrs = $variant->variantAttributes
                    ->map(fn($va) => $va->attributeValue->value ?? '')
                    ->filter()
                    ->implode(' / ');

                OrderItem::create([
                    'order_id'   => $order->id,
                    'variant_id' => $variant->id,
                    'sku'        => $variant->sku,
                    'name'       => $variant->product->name . ($variantAttrs ? ' (' . $variantAttrs . ')' : ''),
                    'quantity'   => $qty,
                    'price'      => $variant->price,
                    'subtotal'   => $subtotal,
                ]);

                // Ghi log lịch sử
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'new_status' => 'PENDING',
                    'note'       => 'Đơn hàng được tạo (Mua ngay)',
                ]);

                if (!$isOnlinePayment) {
                    $this->inventoryService->deduct($variant->id, $qty, $order->id);
                }

                if ($couponData['coupon']) {
                    $this->couponService->recordUsage($couponData['coupon'], $order, auth()->id());
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
            $subtotal = $cart->total;
            $couponData = $this->resolveCoupon($data, $subtotal);

            $selectedItems->loadMissing([
                'variant.product',
                'variant.variantAttributes.attributeValue',
            ]);

            $this->inventoryService->assertManyAvailable(
                $selectedItems->map(fn ($cartItem) => [
                    'variant_id' => $cartItem->variant_id,
                    'quantity'   => $cartItem->quantity,
                    'name'       => $cartItem->variant->product->name,
                ])->all(),
                true
            );

            // 1. Tạo bản ghi Order
            $order = Order::create([
                'user_id'          => auth()->id(),
                'address_id'       => $data['address_id'] ?? null,
                'coupon_id'        => $couponData['coupon']?->id,
                'subtotal'         => $subtotal,
                'discount_amount'  => $couponData['discount_amount'],
                'shipping_fee'     => $data['shipping_fee'] ?? 0,
                'total_price'      => $subtotal + ($data['shipping_fee'] ?? 0) - $couponData['discount_amount'],
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
                    'subtotal'   => $cartItem->variant->price * $cartItem->quantity,
                ]);

                // Chỉ trừ kho ngay với COD — Online payment chờ callback
                if (!$isOnlinePayment) {
                    $this->inventoryService->deduct($cartItem->variant_id, $cartItem->quantity, $order->id);
                }
            }

            if ($couponData['coupon']) {
                $this->couponService->recordUsage($couponData['coupon'], $order, auth()->id());
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
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                throw new Exception('Không tìm thấy đơn hàng thanh toán.');
            }

            if ($lockedOrder->payment_status === 'PAID') {
                $order->setRawAttributes($lockedOrder->getAttributes(), true);
                return false;
            }

            if ($lockedOrder->status === 'CANCELLED') {
                throw new Exception('Đơn hàng đã bị hủy, không thể xác nhận thanh toán.');
            }

            if (! in_array($lockedOrder->payment_method, ['VNPAY', 'MOMO'])) {
                throw new Exception('Phương thức thanh toán không hợp lệ cho xác nhận online.');
            }

            $lockedOrder->load('items');
            $oldStatus = $lockedOrder->status;

            foreach ($lockedOrder->items as $item) {
                $this->inventoryService->deduct($item->variant_id, $item->quantity, $lockedOrder->id);
            }

            $lockedOrder->update([
                'payment_status' => 'PAID',
                'status'         => 'CONFIRMED',
            ]);

            $this->logStatus(
                $lockedOrder->id,
                'CONFIRMED',
                'Thanh toán online thành công. Đơn hàng đã được xác nhận.',
                null,
                $oldStatus
            );

            $this->cartService->clearCart();
            $order->setRawAttributes($lockedOrder->fresh()->getAttributes(), true);

            return true;
        });
    }

    /**
     * Huỷ đơn hàng.
     */
    public function cancelOrder(int $orderId, string $reason, $userId = null, ?int $ownerUserId = null)
    {
        $query = Order::query();

        if ($ownerUserId !== null) {
            $query->where('user_id', $ownerUserId);
        }

        $order = $query->with('items')->find($orderId);

        if (! $order) {
            throw new Exception('Không tìm thấy đơn hàng hoặc bạn không có quyền huỷ đơn hàng này.');
        }

        if (!$order->canBeCancelled()) {
            throw new Exception("Đơn hàng này không thể huỷ ở trạng thái hiện tại.");
        }

        DB::transaction(function () use ($order, $reason, $userId) {
            $oldStatus = $order->status;

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

            $this->logStatus($order->id, 'CANCELLED', "Huỷ đơn hàng. Lý do: {$reason}", $userId, $oldStatus);
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
            $oldStatus = $order->status;
            $updateData = ['status' => $status];
            
            // Nếu đơn hàng hoàn thành, tự động chuyển sang Đã thanh toán
            if ($status === 'COMPLETED') {
                $updateData['payment_status'] = 'PAID';
            }

            $order->update($updateData);
            $this->logStatus($order->id, $status, $note, $userId, $oldStatus);
        });

        return $order;
    }

    /**
     * Ghi lịch sử trạng thái.
     */
    protected function logStatus(int $orderId, string $status, string $note = null, $userId = null, ?string $oldStatus = null)
    {
        if ($oldStatus === null) {
            $order = Order::find($orderId);
            $oldStatus = $order ? $order->status : null;
        }

        OrderStatusHistory::create([
            'order_id'   => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'note'       => $note,
            'changed_by' => $userId ?? auth()->id(),
            'created_at' => now(),
        ]);
    }

    private function resolveCoupon(array $data, float $subtotal): array
    {
        $code = $data['coupon_code'] ?? null;

        if (! $code) {
            return ['coupon' => null, 'discount_amount' => 0.0];
        }

        return $this->couponService->validate($code, auth()->id(), $subtotal, true);
    }
}
