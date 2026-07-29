<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\WishlistService;
use App\Support\UserSafeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    protected $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * Display wishlist.
     */
    public function index()
    {
        $products = $this->wishlistService->getWishlist();
        return view('customer.wishlist.index', compact('products'));
    }

    /**
     * Toggle wishlist via AJAX.
     */
    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        try {
            $isAdded = $this->wishlistService->toggle($request->product_id);
            return response()->json([
                'success' => true,
                'is_added' => $isAdded,
                'message' => $isAdded ? 'Đã thêm vào yêu thích!' : 'Đã xoá khỏi yêu thích!'
            ]);
        } catch (\Exception $e) {
            Log::warning('Wishlist toggle failed', ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => UserSafeMessage::from($e, 'Không thể cập nhật danh sách yêu thích lúc này.'),
            ], UserSafeMessage::statusCode($e));
        }
    }
}
