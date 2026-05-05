<?php

namespace App\Services;

use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    /**
     * Toggle product in wishlist.
     * Returns true if added, false if removed.
     */
    public function toggle(int $productId): bool
    {
        $userId = Auth::id();
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return false;
        }

        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
        return true;
    }

    /**
     * Check if product is in wishlist.
     */
    public function isWishlisted(int $productId): bool
    {
        if (!Auth::check()) return false;

        return Wishlist::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Get user's wishlist products.
     */
    public function getWishlist()
    {
        return Auth::user()->wishlists()
            ->with(['variants.images', 'category', 'brand'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();
    }
}
