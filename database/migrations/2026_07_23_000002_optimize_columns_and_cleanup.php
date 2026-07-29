<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DB Optimization — Cache Columns
 *
 * Mục tiêu:
 * 1. Thêm cột cache `total_stock` vào product_variants
 *    → Không cần JOIN inventory mỗi lần, sync qua InventoryService
 * 2. Thêm cột cache `avg_rating` + `review_count` vào products
 *    → Tránh withAvg/withCount query khi listing sản phẩm
 * 3. Thêm subtotal vào order_items để tối ưu báo cáo
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // 1. products — Thêm cột cache avg_rating & review_count
        //    Cập nhật qua ReviewService khi approve/xóa review
        //    Tránh: withAvg(['reviews'...], 'rating') ở mọi query listing
        // ─────────────────────────────────────────────
        if (!Schema::hasColumn('products', 'avg_rating')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('avg_rating', 3, 2)->default(0.00)
                      ->after('specifications')
                      ->comment('Cache AVG rating, cập nhật khi approve/delete review');
                $table->unsignedInteger('review_count')->default(0)
                      ->after('avg_rating')
                      ->comment('Cache số review đã duyệt');
            });
        }

        // ─────────────────────────────────────────────
        // 2. product_variants — Thêm cột cache total_stock
        //    Cập nhật qua InventoryService khi import/deduct/restore
        //    Tránh: JOIN inventory + SUM(quantity) mỗi lần hiển thị
        // ─────────────────────────────────────────────
        if (!Schema::hasColumn('product_variants', 'total_stock')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->unsignedInteger('total_stock')->default(0)
                      ->after('is_active')
                      ->comment('Cache tổng tồn kho, sync từ InventoryService');
            });

            // Sync data hiện tại từ inventory vào total_stock
            DB::statement("
                UPDATE product_variants pv
                SET pv.total_stock = (
                    SELECT COALESCE(SUM(i.quantity), 0)
                    FROM inventory i
                    WHERE i.variant_id = pv.id
                )
            ");
        }

        // ─────────────────────────────────────────────
        // 3. Sync avg_rating & review_count hiện tại
        // ─────────────────────────────────────────────
        DB::statement("
            UPDATE products p
            SET
                p.avg_rating   = COALESCE((
                    SELECT AVG(r.rating)
                    FROM reviews r
                    WHERE r.product_id = p.id AND r.is_approved = 1
                ), 0.00),
                p.review_count = COALESCE((
                    SELECT COUNT(*)
                    FROM reviews r
                    WHERE r.product_id = p.id AND r.is_approved = 1
                ), 0)
        ");

        // ─────────────────────────────────────────────
        // 4. order_items — Thêm cột subtotal (price * quantity)
        //    Tránh tính toán lại khi query báo cáo / thống kê
        // ─────────────────────────────────────────────
        if (!Schema::hasColumn('order_items', 'subtotal')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('subtotal', 12, 2)->nullable()
                      ->after('quantity')
                      ->comment('= price * quantity, lưu sẵn để tránh tính lại');
            });

            // Sync data hiện tại
            DB::statement("UPDATE order_items SET subtotal = price * quantity WHERE subtotal IS NULL");

            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('subtotal', 12, 2)->nullable(false)->change();
            });
        }

        // Cleanup dữ liệu cũ nên được xử lý bằng scheduled command riêng,
        // không chạy tự động trong migration để tránh mất dữ liệu ngoài ý muốn.
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'avg_rating')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['avg_rating', 'review_count']);
            });
        }

        if (Schema::hasColumn('product_variants', 'total_stock')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('total_stock');
            });
        }

        if (Schema::hasColumn('order_items', 'subtotal')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('subtotal');
            });
        }
    }
};
