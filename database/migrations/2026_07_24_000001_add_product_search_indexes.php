<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->supportsMySqlFullText()) {
            return;
        }

        if (!$this->indexExists('products', 'idx_products_search_fulltext')) {
            DB::statement(
                'ALTER TABLE products ADD FULLTEXT INDEX idx_products_search_fulltext (name, short_desc, description)'
            );
        }

        if (!$this->indexExists('product_variants', 'idx_product_variants_sku')) {
            Schema::table('product_variants', function ($table) {
                $table->index('sku', 'idx_product_variants_sku');
            });
        }
    }

    public function down(): void
    {
        if (!$this->supportsMySqlFullText()) {
            return;
        }

        if ($this->indexExists('products', 'idx_products_search_fulltext')) {
            DB::statement('ALTER TABLE products DROP INDEX idx_products_search_fulltext');
        }

        if ($this->indexExists('product_variants', 'idx_product_variants_sku')) {
            Schema::table('product_variants', function ($table) {
                $table->dropIndex('idx_product_variants_sku');
            });
        }
    }

    private function supportsMySqlFullText(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function indexExists(string $table, string $indexName): bool
    {
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
