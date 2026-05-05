<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CartService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Lấy hoặc tạo giỏ hàng hiện tại cho user hoặc guest.
     */
    public function getOrCreateCart()
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        }

        $sessionId = Session::getId();
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function addItem(int $variantId, int $quantity)
    {
        $cart = $this->getOrCreateCart();
        $variant = ProductVariant::findOrFail($variantId);

        // Kiểm tra tồn kho
        $availableStock = $this->inventoryService->getStock($variantId);
        
        $cartItem = $cart->items()->where('variant_id', $variantId)->first();
        $currentQty = $cartItem ? $cartItem->quantity : 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > $availableStock) {
            throw new \Exception("Không đủ hàng trong kho. Hiện còn: {$availableStock}");
        }

        if ($cartItem) {
            $cartItem->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create([
                'variant_id' => $variantId,
                'quantity' => $quantity
            ]);
        }

        return $cart->load('items.variant.product', 'items.variant.images');
    }

    /**
     * Cập nhật số lượng item.
     */
    public function updateItem(int $itemId, int $quantity)
    {
        $cartItem = CartItem::findOrFail($itemId);
        $availableStock = $this->inventoryService->getStock($cartItem->variant_id);

        if ($quantity > $availableStock) {
            throw new \Exception("Không đủ hàng trong kho. Hiện còn: {$availableStock}");
        }

        if ($quantity <= 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $quantity]);
        }

        return $cartItem->cart->load('items.variant.product', 'items.variant.images');
    }

    /**
     * Xoá item khỏi giỏ hàng.
     */
    public function removeItem(int $itemId)
    {
        $cartItem = CartItem::findOrFail($itemId);
        $cart = $cartItem->cart;
        $cartItem->delete();

        return $cart->load('items.variant.product', 'items.variant.images');
    }

    /**
     * Merge giỏ hàng guest vào user khi login.
     */
    public function mergeCart($userId, $sessionId)
    {
        $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
        if (!$guestCart) return;

        $userCart = Cart::firstOrCreate(['user_id' => $userId]);

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $item) {
                $existingItem = $userCart->items()->where('variant_id', $item->variant_id)->first();
                
                if ($existingItem) {
                    $newQty = $existingItem->quantity + $item->quantity;
                    // Kiểm tra stock khi merge
                    $stock = $this->inventoryService->getStock($item->variant_id);
                    $existingItem->update(['quantity' => min($newQty, $stock)]);
                } else {
                    $item->update(['cart_id' => $userCart->id]);
                }
            }
            $guestCart->delete(); // Xoá cart cũ của guest
        });
    }

    /**
     * Lấy dữ liệu giỏ hàng chuẩn hoá để hiển thị.
     */
    public function getCartData()
    {
        $cart = $this->getOrCreateCart();
        return $cart->load('items.variant.product', 'items.variant.images');
    }

    /**
     * Xoá toàn bộ giỏ hàng (sau khi đặt hàng).
     */
    public function clearCart()
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();
        return $cart;
    }
}
