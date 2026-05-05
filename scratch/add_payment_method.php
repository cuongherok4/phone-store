<?php
use Illuminate\Support\Facades\Schema;

if (!Schema::hasColumn('orders', 'payment_method')) {
    Schema::table('orders', function ($table) {
        $table->string('payment_method')->nullable()->after('payment_status');
    });
}

echo "Column added successfully.";
