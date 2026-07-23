<?php

namespace Tests\Unit\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('customer');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('PENDING');
            $table->timestamps();
        });
    }

    public function test_customer_can_view_own_order(): void
    {
        $user = User::create(['name' => 'Customer', 'email' => 'customer@example.com', 'role' => 'customer']);
        $order = Order::create(['user_id' => $user->id]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $order));
    }

    public function test_customer_cannot_view_another_users_order(): void
    {
        $user = User::create(['name' => 'Customer', 'email' => 'customer@example.com', 'role' => 'customer']);
        $other = User::create(['name' => 'Other', 'email' => 'other@example.com', 'role' => 'customer']);
        $order = Order::create(['user_id' => $other->id]);

        $this->assertFalse(Gate::forUser($user)->allows('view', $order));
    }

    public function test_admin_can_view_any_order(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin']);
        $customer = User::create(['name' => 'Customer', 'email' => 'customer@example.com', 'role' => 'customer']);
        $order = Order::create(['user_id' => $customer->id]);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $order));
    }
}
