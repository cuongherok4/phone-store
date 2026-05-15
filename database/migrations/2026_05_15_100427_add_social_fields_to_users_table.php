<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('provider')->nullable()->after('role');
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('provider_token')->nullable()->after('provider_id');
            $table->string('provider_refresh_token')->nullable()->after('provider_token');
            
            $table->index('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
            $table->dropColumn(['provider', 'provider_id', 'provider_token', 'provider_refresh_token']);
        });
    }
};
