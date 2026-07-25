<?php

namespace Tests\Feature\Customer;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('reviews');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('variant_images');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('user_addresses');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->default('customer');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });

        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->boolean('is_selected')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('status')->default('PENDING');
            $table->string('payment_status')->default('UNPAID');
            $table->string('payment_method')->default('COD');
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('variant_id');
            $table->string('sku')->nullable();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 12, 2)->default(0);
        });

        Schema::create('variant_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->string('image_url')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_customer_can_view_their_own_order_detail(): void
    {
        $customer = $this->createCustomer('customer@example.com');
        $order = $this->createOrderFor($customer);

        $this->actingAs($customer)
            ->get(route('orders.show', $order->id))
            ->assertOk()
            ->assertSee('Chi tiết đơn hàng #' . $order->id);
    }

    public function test_customer_cannot_view_another_customers_order_detail(): void
    {
        $owner = $this->createCustomer('owner@example.com');
        $otherCustomer = $this->createCustomer('other@example.com');
        $order = $this->createOrderFor($owner);

        $this->actingAs($otherCustomer)
            ->get(route('orders.show', $order->id))
            ->assertForbidden();
    }

    private function createCustomer(string $email): User
    {
        return User::create([
            'name' => 'Customer',
            'email' => $email,
            'role' => 'customer',
        ]);
    }

    private function createOrderFor(User $user): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => 10000000,
            'shipping_fee' => 0,
            'total_price' => 10000000,
            'status' => 'PENDING',
            'payment_status' => 'UNPAID',
            'payment_method' => 'COD',
            'shipping_name' => 'Nguyen Van A',
            'shipping_phone' => '0900000000',
            'shipping_address' => 'Ha Noi',
        ]);

        $product = Product::create([
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'IP15-128',
            'price' => 10000000,
            'is_active' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'sku' => 'IP15-128',
            'name' => 'iPhone 15',
            'price' => 10000000,
            'quantity' => 1,
            'subtotal' => 10000000,
        ]);

        DB::table('order_status_histories')->insert([
            'order_id' => $order->id,
            'new_status' => 'PENDING',
            'note' => 'Đơn hàng đã được tạo.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $order;
    }
}
