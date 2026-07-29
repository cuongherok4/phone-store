<?php

namespace App\Services;

use App\Exceptions\Domain\InvalidCouponException;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;

class CouponService
{
    public function validate(string $code, int $userId, float $subtotal, bool $lockForUpdate = false): array
    {
        if ($userId <= 0) {
            throw new InvalidCouponException('Vui lòng đăng nhập để sử dụng mã giảm giá.');
        }

        if ($subtotal <= 0) {
            throw new InvalidCouponException('Giá trị đơn hàng không hợp lệ để dùng mã giảm giá.');
        }

        $query = Coupon::where('code', strtoupper(trim($code)));

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();

        if (! $coupon) {
            throw new InvalidCouponException('Mã giảm giá không tồn tại.');
        }

        if (! $coupon->is_active) {
            throw new InvalidCouponException('Mã giảm giá đang tạm dừng.');
        }

        if ($coupon->start_at && now()->lt($coupon->start_at)) {
            throw new InvalidCouponException('Mã giảm giá chưa đến thời gian sử dụng.');
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            throw new InvalidCouponException('Mã giảm giá đã hết hạn.');
        }

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            throw new InvalidCouponException('Mã giảm giá đã hết lượt sử dụng.');
        }

        if ($subtotal < (float) $coupon->min_order_value) {
            throw new InvalidCouponException('Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.');
        }

        $userUsageCount = CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->count();

        if ($coupon->max_uses_per_user && $userUsageCount >= $coupon->max_uses_per_user) {
            throw new InvalidCouponException('Bạn đã dùng hết lượt cho mã giảm giá này.');
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => $this->calculate($coupon, $subtotal),
        ];
    }

    public function calculate(Coupon $coupon, float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

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
        if ($userId <= 0) {
            throw new InvalidCouponException('Không thể ghi nhận mã giảm giá cho người dùng không hợp lệ.');
        }

        $usage = CouponUsage::firstOrCreate(
            [
                'coupon_id' => $coupon->id,
                'order_id' => $order->id,
            ],
            [
                'user_id' => $userId,
                'used_at' => now(),
            ]
        );

        if ($usage->wasRecentlyCreated) {
            $coupon->increment('used_count');
        }
    }
}
