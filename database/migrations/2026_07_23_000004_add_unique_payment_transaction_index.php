<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('payments')) {
            return;
        }

        $existingIndex = DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_payments_transaction'");

        if ($existingIndex && (int) $existingIndex[0]->Non_unique === 1) {
            DB::statement('ALTER TABLE payments DROP INDEX idx_payments_transaction');
        }

        if (! $existingIndex || (int) $existingIndex[0]->Non_unique === 1) {
            DB::statement('ALTER TABLE payments ADD UNIQUE INDEX idx_payments_transaction (transaction_id)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('payments')) {
            return;
        }

        $existingIndex = DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_payments_transaction'");

        if ($existingIndex && (int) $existingIndex[0]->Non_unique === 0) {
            DB::statement('ALTER TABLE payments DROP INDEX idx_payments_transaction');
            DB::statement('ALTER TABLE payments ADD INDEX idx_payments_transaction (transaction_id)');
        }
    }
};
