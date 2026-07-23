<?php

namespace Tests\Unit\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Services\CouponService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    private CouponService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('discount_type');
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->decimal('min_order_value', 12, 2)->default(0);
            $table->integer('max_uses')->nullable();
            $table->integer('max_uses_per_user')->default(1);
            $table->integer('used_count')->default(0);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->unique(['coupon_id', 'order_id']);
        });

        $this->service = app(CouponService::class);
    }

    public function test_percent_coupon_respects_max_discount_amount(): void
    {
        $coupon = Coupon::create([
            'code' => 'sale10',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'max_discount_amount' => 50_000,
            'min_order_value' => 0,
            'max_uses_per_user' => 1,
            'is_active' => true,
        ]);

        $result = $this->service->validate('sale10', 1, 1_000_000);

        $this->assertSame($coupon->id, $result['coupon']->id);
        $this->assertSame(50_000.0, $result['discount_amount']);
    }

    public function test_fixed_coupon_never_exceeds_subtotal(): void
    {
        $coupon = Coupon::create([
            'code' => 'FIXED',
            'discount_type' => 'fixed',
            'discount_value' => 200_000,
            'min_order_value' => 0,
            'max_uses_per_user' => 1,
            'is_active' => true,
        ]);

        $this->assertSame(150_000.0, $this->service->calculate($coupon, 150_000));
    }

    public function test_coupon_requires_minimum_order_value(): void
    {
        Coupon::create([
            'code' => 'MIN500',
            'discount_type' => 'fixed',
            'discount_value' => 50_000,
            'min_order_value' => 500_000,
            'max_uses_per_user' => 1,
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('giá trị tối thiểu');

        $this->service->validate('MIN500', 1, 300_000);
    }

    public function test_coupon_requires_authenticated_user(): void
    {
        Coupon::create([
            'code' => 'LOGIN',
            'discount_type' => 'fixed',
            'discount_value' => 50_000,
            'min_order_value' => 0,
            'max_uses_per_user' => 1,
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('đăng nhập');

        $this->service->validate('LOGIN', 0, 500_000);
    }

    public function test_coupon_requires_positive_subtotal(): void
    {
        Coupon::create([
            'code' => 'TOTAL',
            'discount_type' => 'fixed',
            'discount_value' => 50_000,
            'min_order_value' => 0,
            'max_uses_per_user' => 1,
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('không hợp lệ');

        $this->service->validate('TOTAL', 1, 0);
    }

    public function test_coupon_rejects_user_usage_limit(): void
    {
        $coupon = Coupon::create([
            'code' => 'ONCE',
            'discount_type' => 'fixed',
            'discount_value' => 50_000,
            'min_order_value' => 0,
            'max_uses_per_user' => 1,
            'is_active' => true,
        ]);

        \DB::table('coupon_usages')->insert([
            'coupon_id' => $coupon->id,
            'user_id' => 1,
            'order_id' => null,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('dùng hết lượt');

        $this->service->validate('ONCE', 1, 500_000);
    }

    public function test_record_usage_creates_usage_and_increments_used_count(): void
    {
        $coupon = Coupon::create([
            'code' => 'SAVE',
            'discount_type' => 'fixed',
            'discount_value' => 50_000,
            'min_order_value' => 0,
            'max_uses_per_user' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);
        $order = Order::create(['user_id' => 1]);

        $this->service->recordUsage($coupon, $order, 1);

        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'user_id' => 1,
        ]);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_record_usage_is_idempotent_for_same_order(): void
    {
        $coupon = Coupon::create([
            'code' => 'ONETIME',
            'discount_type' => 'fixed',
            'discount_value' => 50_000,
            'min_order_value' => 0,
            'max_uses_per_user' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);
        $order = Order::create(['user_id' => 1]);

        $this->service->recordUsage($coupon, $order, 1);
        $this->service->recordUsage($coupon->fresh(), $order, 1);

        $this->assertSame(1, \DB::table('coupon_usages')->count());
        $this->assertSame(1, $coupon->fresh()->used_count);
    }
}
