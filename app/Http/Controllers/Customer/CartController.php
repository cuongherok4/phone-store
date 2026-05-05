<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

/**
 * CartController — Stub cho 3.2
 * Logic đầy đủ (CartService, merge guest cart, validate stock) sẽ hoàn thiện ở mục 3.3.
 * Hiện tại: lưu giỏ hàng vào Session, hỗ trợ cả guest và user.
 */
class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Trang giỏ hàng.
     */
    public function index()
    {
        $cart = $this->cartService->getCartData();
        return view('customer.cart.index', compact('cart'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1|max:99',
        ]);

        try {
            $this->cartService->addItem(
                $request->variant_id,
                $request->quantity
            );

            if ($request->expectsJson()) {
                $cart = $this->cartService->getOrCreateCart();
                return response()->json([
                    'success'    => true,
                    'message'    => 'Đã thêm vào giỏ hàng!',
                    'cart_count' => $cart->item_count,
                ]);
            }

            return back()->with('success', '✅ Đã thêm sản phẩm vào giỏ hàng!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cập nhật số lượng.
     */
    public function update(Request $request, $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:0|max:99']);

        try {
            $cart = $this->cartService->updateItem($itemId, $request->quantity);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'    => true,
                    'cart_count' => $cart->item_count,
                    'total'      => number_format($cart->total, 0, ',', '.') . 'đ',
                ]);
            }

            return back();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Xoá khỏi giỏ hàng.
     */
    public function remove($itemId)
    {
        $cart = $this->cartService->removeItem($itemId);

        if (request()->expectsJson()) {
            return response()->json([
                'success'    => true,
                'cart_count' => $cart->item_count,
                'total'      => number_format($cart->total, 0, ',', '.') . 'đ',
            ]);
        }

        return back()->with('success', 'Đã xoá sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Lấy số lượng item trong giỏ (AJAX).
     */
    public function count()
    {
        $cart = $this->cartService->getOrCreateCart();
        return response()->json(['count' => $cart->item_count]);
    }
}
