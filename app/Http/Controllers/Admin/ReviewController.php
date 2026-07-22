<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Danh sách đánh giá.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product'])->latest();

        if ($request->filled('status')) {
            $status = $request->status === 'approved';
            $query->where('is_approved', $status);
        } else {
            // Mặc định hiện cái chưa duyệt lên đầu
            $query->orderBy('is_approved', 'asc');
        }

        $reviews = $query->paginate(15);
        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Duyệt đánh giá.
     */
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);

        // Cập nhật lại cache rating của product
        $review->product?->syncReviewStats();

        return back()->with('success', 'Đã duyệt đánh giá.');
    }

    /**
     * Xoá đánh giá.
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $product = $review->product;
        $review->delete();

        // Cập nhật lại cache rating của product
        $product?->syncReviewStats();

        return back()->with('success', 'Đã xoá đánh giá.');
    }
}
