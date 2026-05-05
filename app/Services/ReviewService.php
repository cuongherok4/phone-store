<?php

namespace App\Services;

use App\Models\Review;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Exception;

class ReviewService
{
    /**
     * Submit a new review.
     */
    public function submit(array $data)
    {
        $userId = Auth::id();
        
        // 1. Kiểm tra xem người dùng đã mua sản phẩm này chưa
        $orderItem = OrderItem::where('id', $data['order_item_id'])
            ->whereHas('order', function($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'COMPLETED');
            })->first();

        if (!$orderItem) {
            throw new Exception("Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua hàng thành công.");
        }

        // 2. Kiểm tra xem đã đánh giá chưa
        if (Review::where('order_item_id', $data['order_item_id'])->exists()) {
            throw new Exception("Bạn đã đánh giá sản phẩm này rồi.");
        }

        // 3. Tạo review (mặc định chờ duyệt)
        return Review::create([
            'product_id'    => $orderItem->variant->product_id,
            'user_id'       => $userId,
            'order_item_id' => $data['order_item_id'],
            'rating'        => $data['rating'],
            'comment'       => $data['comment'],
            'images'        => $data['images'] ?? [],
            'is_approved'   => false, // Chờ admin duyệt
        ]);
    }

    /**
     * Lấy các sản phẩm chờ đánh giá của user.
     */
    public function getPendingReviews()
    {
        $userId = Auth::id();
        
        return OrderItem::whereHas('order', function($q) use ($userId) {
            $q->where('user_id', $userId)->where('status', 'COMPLETED');
        })
        ->whereDoesntHave('review')
        ->with(['variant.product', 'variant.images'])
        ->get();
    }
}
