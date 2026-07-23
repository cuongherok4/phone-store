<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('coupon_usages')) {
            return;
        }

        $index = DB::select("SHOW INDEX FROM coupon_usages WHERE Key_name = 'idx_coupon_usage_order_unique'");

        if (! $index) {
            DB::statement('ALTER TABLE coupon_usages ADD UNIQUE INDEX idx_coupon_usage_order_unique (coupon_id, order_id)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('coupon_usages')) {
            return;
        }

        $index = DB::select("SHOW INDEX FROM coupon_usages WHERE Key_name = 'idx_coupon_usage_order_unique'");

        if ($index) {
            DB::statement('ALTER TABLE coupon_usages DROP INDEX idx_coupon_usage_order_unique');
        }
    }
};
