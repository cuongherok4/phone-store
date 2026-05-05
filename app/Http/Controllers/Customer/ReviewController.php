<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Danh sách sản phẩm chờ đánh giá.
     */
    public function index()
    {
        $pendingReviews = $this->reviewService->getPendingReviews();
        return view('customer.reviews.index', compact('pendingReviews'));
    }

    /**
     * Xử lý gửi đánh giá.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'nullable|string|max:1000',
            'images.*'      => 'nullable|image|max:2048',
        ]);

        try {
            $data = $request->only(['order_item_id', 'rating', 'comment']);
            
            // Xử lý upload ảnh (nếu có)
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $images[] = $file->store('reviews', 'public');
                }
            }
            $data['images'] = $images;

            $this->reviewService->submit($data);

            return back()->with('success', 'Cảm ơn bạn đã đánh giá! Nhận xét của bạn đang chờ quản trị viên duyệt.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
