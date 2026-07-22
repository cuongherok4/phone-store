<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use Exception;

class CouponService
{
    public function validate(string $code, int $userId, float $subtotal, bool $lockForUpdate = false): array
    {
        $query = Coupon::where('code', strtoupper(trim($code)));

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();

        if (! $coupon) {
            throw new Exception('Mã giảm giá không tồn tại.');
        }

        if (! $coupon->is_active) {
            throw new Exception('Mã giảm giá đang tạm dừng.');
        }

        if ($coupon->start_at && now()->lt($coupon->start_at)) {
            throw new Exception('Mã giảm giá chưa đến thời gian sử dụng.');
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            throw new Exception('Mã giảm giá đã hết hạn.');
        }

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            throw new Exception('Mã giảm giá đã hết lượt sử dụng.');
        }

        if ($subtotal < (float) $coupon->min_order_value) {
            throw new Exception('Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.');
        }

        $userUsageCount = CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->count();

        if ($coupon->max_uses_per_user && $userUsageCount >= $coupon->max_uses_per_user) {
            throw new Exception('Bạn đã dùng hết lượt cho mã giảm giá này.');
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => $this->calculate($coupon, $subtotal),
        ];
    }

    public function calculate(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->discount_type === 'percent') {
            $discount = $subtotal * ((float) $coupon->discount_value / 100);

            if ($coupon->max_discount_amount) {
                $discount = min($discount, (float) $coupon->max_discount_amount);
            }

            return min($discount, $subtotal);
        }

        return min((float) $coupon->discount_value, $subtotal);
    }

    public function recordUsage(Coupon $coupon, Order $order, int $userId): void
    {
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $userId,
            'order_id' => $order->id,
        ]);

        $coupon->increment('used_count');
    }
}
