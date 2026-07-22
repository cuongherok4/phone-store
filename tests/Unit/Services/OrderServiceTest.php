<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Services\CartService;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    private OrderService $service;
    private $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('status')->default('PENDING');
            $table->string('payment_status')->default('UNPAID');
            $table->string('payment_method')->default('VNPAY');
            $table->string('cancelled_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->integer('quantity')->default(1);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        $this->inventoryService = Mockery::mock(InventoryService::class);
        $this->service = new OrderService(Mockery::mock(CartService::class), $this->inventoryService);
    }

    public function test_customer_cannot_cancel_another_users_order(): void
    {
        $order = Order::create([
            'user_id' => 2,
            'status' => 'PENDING',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('không có quyền');

        $this->service->cancelOrder($order->id, 'Không muốn mua nữa', 1, 1);
    }

    public function test_customer_can_cancel_own_pending_order(): void
    {
        $order = Order::create([
            'user_id' => 1,
            'status' => 'PENDING',
        ]);

        $this->service->cancelOrder($order->id, 'Đổi ý', 1, 1);

        $order->refresh();

        $this->assertSame('CANCELLED', $order->status);
        $this->assertSame('Đổi ý', $order->cancelled_reason);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'old_status' => 'PENDING',
            'new_status' => 'CANCELLED',
            'changed_by' => 1,
        ]);
    }

    public function test_customer_cannot_cancel_completed_order(): void
    {
        $order = Order::create([
            'user_id' => 1,
            'status' => 'COMPLETED',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('không thể huỷ');

        $this->service->cancelOrder($order->id, 'Đổi ý', 1, 1);
    }

    public function test_admin_cancel_restores_stock_for_cod_order(): void
    {
        $order = Order::create([
            'user_id' => 1,
            'status' => 'CONFIRMED',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);

        \DB::table('order_items')->insert([
            'order_id' => $order->id,
            'variant_id' => 10,
            'quantity' => 2,
        ]);

        $this->inventoryService
            ->shouldReceive('restore')
            ->once()
            ->with(10, 2, $order->id);

        $this->service->cancelOrder($order->id, 'Admin huỷ đơn: Khách đổi địa chỉ', 99);

        $order->refresh();

        $this->assertSame('CANCELLED', $order->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'old_status' => 'CONFIRMED',
            'new_status' => 'CANCELLED',
            'changed_by' => 99,
        ]);
    }
}
