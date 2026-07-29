<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE inventory_logs
            MODIFY change_type ENUM('IMPORT','EXPORT','ADJUST','RETURN') NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE inventory_logs SET change_type = 'IMPORT' WHERE change_type = 'RETURN'");
        DB::statement("
            ALTER TABLE inventory_logs
            MODIFY change_type ENUM('IMPORT','EXPORT','ADJUST') NOT NULL
        ");
    }
};
