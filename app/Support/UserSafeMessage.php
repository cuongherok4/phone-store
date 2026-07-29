<?php

namespace App\Support;

use App\Exceptions\Domain\DomainException;
use Throwable;

class UserSafeMessage
{
    private const FALLBACK = 'Có lỗi xảy ra, vui lòng thử lại sau.';

    private const SAFE_PREFIXES = [
        'Không đủ hàng trong kho.',
        'Sản phẩm',
        'Vui lòng chọn sản phẩm',
        'Mã giảm giá',
        'Đơn hàng',
        'Không thể',
        'Bạn đã đánh giá',
        'Bạn không thể',
        'Bạn chưa mua',
        'Số lượng',
    ];

    public static function from(Throwable $exception, ?string $fallback = null): string
    {
        if ($exception instanceof DomainException) {
            return $exception->getMessage();
        }

        $message = trim($exception->getMessage());

        foreach (self::SAFE_PREFIXES as $prefix) {
            if (str_starts_with($message, $prefix)) {
                return $message;
            }
        }

        return $fallback ?? self::FALLBACK;
    }

    public static function statusCode(Throwable $exception, int $fallback = 500): int
    {
        if ($exception instanceof DomainException) {
            return $exception->statusCode();
        }

        return $fallback;
    }
}
