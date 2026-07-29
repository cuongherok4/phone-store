<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cart_items') || $this->indexExists('cart_items', 'uq_cart_items_cart_variant')) {
            return;
        }

        DB::table('cart_items')
            ->select('cart_id', 'variant_id', DB::raw('MIN(id) as keep_id'), DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('cart_id', 'variant_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('keep_id')
            ->get()
            ->each(function ($duplicate) {
                DB::table('cart_items')
                    ->where('id', $duplicate->keep_id)
                    ->update([
                        'quantity' => $duplicate->total_quantity,
                        'is_selected' => true,
                    ]);

                DB::table('cart_items')
                    ->where('cart_id', $duplicate->cart_id)
                    ->where('variant_id', $duplicate->variant_id)
                    ->where('id', '!=', $duplicate->keep_id)
                    ->delete();
            });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'variant_id'], 'uq_cart_items_cart_variant');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('cart_items') || !$this->indexExists('cart_items', 'uq_cart_items_cart_variant')) {
            return;
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('uq_cart_items_cart_variant');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($index) => $index->name === $indexName);
        }

        $result = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = ?
              AND INDEX_NAME   = ?
        ", [$table, $indexName]);

        return (int) $result[0]->cnt > 0;
    }
};
