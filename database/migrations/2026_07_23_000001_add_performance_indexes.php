<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DB Optimization Migration
 *
 * Mục tiêu: Tối ưu hiệu năng truy vấn bằng cách thêm/sửa indexes
 * phù hợp với các query thực tế trong hệ thống.
 *
 * Phân tích query patterns:
 * - Product listing: filter status+deleted_at, filter brand, sort by price
 * - Product detail: lookup by slug
 * - Cart: lookup by user_id, session_id
 * - Orders: filter by user+status, filter by created_at (báo cáo)
 * - Inventory: sum quantity by variant_id (thường xuyên)
 * - Notifications: count unread by user_id
 * - Reviews: avg rating by product_id + is_approved
 * - Inventory logs: filter by variant_id + created_at (lịch sử kho)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // PRODUCTS — Các query phổ biến nhất
        // ─────────────────────────────────────────────

        // Trang danh sách: WHERE status=1 AND deleted_at IS NULL
        // → Index (status, deleted_at) đã có trong SQL gốc, nhưng thiếu brand_id
        // Trang lọc theo brand: WHERE brand_id IN (...) AND status=1 AND deleted_at IS NULL
        if (!$this->indexExists('products', 'idx_products_brand_status_deleted')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['brand_id', 'status', 'deleted_at'], 'idx_products_brand_status_deleted');
            });
        }

        // Trang chi tiết: WHERE slug = ? AND status = 1 AND deleted_at IS NULL
        // slug đã là UNIQUE (tự có index), nhưng thêm covering index nhanh hơn
        if (!$this->indexExists('products', 'idx_products_slug_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['slug', 'status', 'deleted_at'], 'idx_products_slug_status');
            });
        }

        // ─────────────────────────────────────────────
        // PRODUCT VARIANTS — Query tồn kho + lọc giá
        // ─────────────────────────────────────────────

        // Lọc theo giá: WHERE product_id=? AND is_active=1 AND deleted_at IS NULL ORDER BY price
        if (!$this->indexExists('product_variants', 'idx_variants_product_active_price')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->index(['product_id', 'is_active', 'deleted_at', 'price'], 'idx_variants_product_active_price');
            });
        }

        // ─────────────────────────────────────────────
        // VARIANT IMAGES — Query ảnh chính
        // ─────────────────────────────────────────────

        // WHERE variant_id=? ORDER BY sort_order (load ảnh sản phẩm)
        if (!$this->indexExists('variant_images', 'idx_variant_images_variant_sort')) {
            Schema::table('variant_images', function (Blueprint $table) {
                $table->index(['variant_id', 'sort_order', 'is_primary'], 'idx_variant_images_variant_sort');
            });
        }

        // ─────────────────────────────────────────────
        // INVENTORY — Query sum tồn kho (rất thường xuyên)
        // ─────────────────────────────────────────────

        // SELECT SUM(quantity) WHERE variant_id=? → covering index
        if (!$this->indexExists('inventory', 'idx_inventory_variant_qty')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->index(['variant_id', 'quantity'], 'idx_inventory_variant_qty');
            });
        }

        // ─────────────────────────────────────────────
        // INVENTORY LOGS — Query lịch sử kho
        // ─────────────────────────────────────────────

        // WHERE variant_id=? ORDER BY created_at DESC (xem lịch sử)
        if (!$this->indexExists('inventory_logs', 'idx_inv_logs_variant_created')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                $table->index(['variant_id', 'created_at'], 'idx_inv_logs_variant_created');
            });
        }

        // WHERE change_type='EXPORT' AND reference_id=? (khi hủy đơn)
        if (!$this->indexExists('inventory_logs', 'idx_inv_logs_ref')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                $table->index(['reference_type', 'reference_id'], 'idx_inv_logs_ref');
            });
        }

        // ─────────────────────────────────────────────
        // ORDERS — Báo cáo + lịch sử đơn
        // ─────────────────────────────────────────────

        // Dashboard: GROUP BY DATE(created_at) - lọc khoảng ngày
        if (!$this->indexExists('orders', 'idx_orders_status_created')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_orders_status_created');
            });
        }

        // Lọc đơn hàng: WHERE user_id=? AND status=? (lịch sử đơn customer)
        // idx_orders_user_status đã có trong SQL gốc ✅

        // ─────────────────────────────────────────────
        // ORDER ITEMS — Query sản phẩm bán chạy
        // ─────────────────────────────────────────────

        // SELECT variant_id, SUM(quantity) GROUP BY variant_id (top products)
        if (!$this->indexExists('order_items', 'idx_order_items_variant_qty')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->index(['variant_id', 'quantity'], 'idx_order_items_variant_qty');
            });
        }

        // ─────────────────────────────────────────────
        // REVIEWS — Rating trung bình sản phẩm
        // ─────────────────────────────────────────────

        // WHERE product_id=? AND is_approved=1 (hiển thị review + tính avg)
        // idx_reviews_product(product_id, is_approved) đã có ✅
        // Thêm covering index cho AVG(rating)
        if (!$this->indexExists('reviews', 'idx_reviews_product_approved_rating')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index(['product_id', 'is_approved', 'rating'], 'idx_reviews_product_approved_rating');
            });
        }

        // ─────────────────────────────────────────────
        // CARTS & CART ITEMS
        // ─────────────────────────────────────────────

        // idx_cart_user, idx_cart_session đã có ✅
        // Cart items: WHERE cart_id=? AND is_selected=1
        if (!$this->indexExists('cart_items', 'idx_cart_items_selected')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index(['cart_id', 'is_selected'], 'idx_cart_items_selected');
            });
        }

        // ─────────────────────────────────────────────
        // NOTIFICATIONS — Badge count (query mỗi request)
        // ─────────────────────────────────────────────

        // SELECT COUNT(*) WHERE user_id=? AND is_read=0
        // idx_notifications_user(user_id, is_read) đã có ✅

        // ─────────────────────────────────────────────
        // COUPONS — Validate mã giảm giá
        // ─────────────────────────────────────────────

        // WHERE code=? (đã là UNIQUE, có index ✅)
        // WHERE is_active=1 AND start_at<=NOW() AND expires_at>=NOW()
        if (!$this->indexExists('coupons', 'idx_coupons_active_dates')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index(['is_active', 'start_at', 'expires_at'], 'idx_coupons_active_dates');
            });
        }

        // ─────────────────────────────────────────────
        // ORDER STATUS HISTORIES — Timeline đơn hàng
        // ─────────────────────────────────────────────

        // WHERE order_id=? ORDER BY created_at ASC
        if (!$this->indexExists('order_status_histories', 'idx_osh_order_created')) {
            Schema::table('order_status_histories', function (Blueprint $table) {
                $table->index(['order_id', 'created_at'], 'idx_osh_order_created');
            });
        }

        // ─────────────────────────────────────────────
        // USER ADDRESSES — Checkout lookup
        // ─────────────────────────────────────────────

        // WHERE user_id=? ORDER BY is_default DESC
        if (!$this->indexExists('user_addresses', 'idx_user_addresses_user_default')) {
            Schema::table('user_addresses', function (Blueprint $table) {
                $table->index(['user_id', 'is_default'], 'idx_user_addresses_user_default');
            });
        }

        // ─────────────────────────────────────────────
        // WISHLISTS — Toggle & danh sách yêu thích
        // ─────────────────────────────────────────────

        // WHERE user_id=? AND product_id=? (toggle)
        // PRIMARY KEY (user_id, product_id) đã cover ✅
        // Thêm index ngược product_id→user_id (check ai đã wishlist)
        if (!$this->indexExists('wishlists', 'idx_wishlists_product')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->index(['product_id'], 'idx_wishlists_product');
            });
        }

        // ─────────────────────────────────────────────
        // SETTINGS — Lookup theo key (rất thường xuyên)
        // ─────────────────────────────────────────────

        // key đã là UNIQUE ✅ — Không cần thêm
    }

    public function down(): void
    {
        $indexes = [
            'products'               => ['idx_products_brand_status_deleted', 'idx_products_slug_status'],
            'product_variants'       => ['idx_variants_product_active_price'],
            'variant_images'         => ['idx_variant_images_variant_sort'],
            'inventory'              => ['idx_inventory_variant_qty'],
            'inventory_logs'         => ['idx_inv_logs_variant_created', 'idx_inv_logs_ref'],
            'orders'                 => ['idx_orders_status_created'],
            'order_items'            => ['idx_order_items_variant_qty'],
            'reviews'                => ['idx_reviews_product_approved_rating'],
            'cart_items'             => ['idx_cart_items_selected'],
            'coupons'                => ['idx_coupons_active_dates'],
            'order_status_histories' => ['idx_osh_order_created'],
            'user_addresses'         => ['idx_user_addresses_user_default'],
            'wishlists'              => ['idx_wishlists_product'],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            Schema::table($table, function (Blueprint $t) use ($tableIndexes) {
                foreach ($tableIndexes as $index) {
                    if ($this->indexExists($t->getTable(), $index)) {
                        $t->dropIndex($index);
                    }
                }
            });
        }
    }

    /**
     * Kiểm tra index đã tồn tại chưa (tránh lỗi duplicate key khi chạy lại)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = ?
              AND INDEX_NAME   = ?
        ", [$table, $indexName]);

        return $result[0]->cnt > 0;
    }
};
