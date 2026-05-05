<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image_url');
            $table->string('link_url')->nullable();
            $table->enum('type', ['MAIN', 'SECONDARY'])->default('MAIN');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert initial settings
        DB::table('settings')->insert([
            [
                'key' => 'store_phone',
                'value' => '0123.456.789',
                'group' => 'store',
                'description' => 'Số điện thoại hiển thị trên header',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'vnpay_tmn_code',
                'value' => '',
                'group' => 'vnpay',
                'description' => 'Mã định danh website tại hệ thống VNPAY',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'vnpay_hash_secret',
                'value' => '',
                'group' => 'vnpay',
                'description' => 'Chuỗi bí mật dùng để tạo mã hash từ VNPAY',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'vnpay_url',
                'value' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
                'group' => 'vnpay',
                'description' => 'URL cổng thanh toán VNPAY',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('settings');
    }
};
