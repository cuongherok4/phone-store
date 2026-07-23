<?php

namespace Tests\Feature\Customer;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('variant_images');
        Schema::dropIfExists('attribute_values');
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

        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('PENDING');
            $table->string('payment_status')->default('UNPAID');
            $table->string('payment_method')->default('COD');
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
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('variant_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->string('image_url')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->string('value');
        });

        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('attribute_value_id')->nullable();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->nullable();
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });
    }

    public function test_cod_checkout_process_creates_order_and_redirects_to_success(): void
    {
        $user = $this->actingCustomer();
        $order = Order::create([
            'user_id' => $user->id,
            'payment_method' => 'COD',
        ]);
        $order->setRelation('user', $user);

        $this->mock(OrderService::class, function ($mock) use ($order) {
            $mock->shouldReceive('createFromCart')
                ->once()
                ->with(Mockery::on(fn ($data) => $data['payment_method'] === 'COD' && $data['shipping_fee'] === 0))
                ->andReturn($order);
        });

        $response = $this->actingAs($user)->post(route('checkout.process'), $this->checkoutPayload('COD'));

        $response->assertRedirect(route('checkout.success', $order->id));
    }

    public function test_direct_buy_checkout_redirects_when_stock_is_not_enough(): void
    {
        $user = $this->actingCustomer();
        $variant = $this->createVariant('iPhone 15', 'iphone-15', 12_000_000);

        $this->mock(CartService::class, function ($mock) {
            $mock->shouldReceive('getCartData')
                ->once()
                ->andReturn(new Cart());
        });
        $this->mock(InventoryService::class, function ($mock) use ($variant) {
            $mock->shouldReceive('getStock')
                ->once()
                ->with($variant->id)
                ->andReturn(1);
        });

        $response = $this->actingAs($user)->get(route('checkout.index', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]));

        $response->assertRedirect(route('customer.products.show', 'iphone-15'));
        $response->assertSessionHas('error');
    }

    public function test_coupon_check_uses_server_side_direct_buy_subtotal(): void
    {
        $user = $this->actingCustomer();
        $variant = $this->createVariant('iPhone 15', 'iphone-15', 12_000_000);

        $coupon = new Coupon([
            'code' => 'SAVE10',
            'discount_type' => 'percent',
            'discount_value' => 10,
        ]);
        $coupon->id = 7;

        $this->mock(CouponService::class, function ($mock) use ($user, $coupon) {
            $mock->shouldReceive('validate')
                ->once()
                ->with('SAVE10', $user->id, 24_000_000.0)
                ->andReturn([
                    'coupon' => $coupon,
                    'discount_amount' => 100_000.0,
                ]);
        });

        $response = $this->actingAs($user)->postJson(route('checkout.check_coupon'), [
            'code' => 'SAVE10',
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('discount_amount', 100_000)
            ->assertJsonPath('coupon.code', 'SAVE10');
    }

    public function test_coupon_check_returns_validation_message_when_coupon_is_rejected(): void
    {
        $user = $this->actingCustomer();

        $cart = $this->makeCartWithTotal(300_000);

        $this->mock(CartService::class, function ($mock) use ($cart) {
            $mock->shouldReceive('getCartData')
                ->once()
                ->andReturn($cart);
        });
        $this->mock(CouponService::class, function ($mock) use ($user) {
            $mock->shouldReceive('validate')
                ->once()
                ->with('MIN500', $user->id, 300_000)
                ->andThrow(new \Exception('Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.'));
        });

        $response = $this->actingAs($user)->postJson(route('checkout.check_coupon'), [
            'code' => 'MIN500',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.');
    }

    private function actingCustomer(): User
    {
        return User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]);
    }

    private function checkoutPayload(string $paymentMethod): array
    {
        return [
            'shipping_name' => 'Nguyen Van A',
            'shipping_phone' => '0900000000',
            'shipping_address' => '123 Nguyen Trai',
            'payment_method' => $paymentMethod,
        ];
    }

    private function createVariant(string $productName, string $slug, float $price): ProductVariant
    {
        $product = Product::create([
            'name' => $productName,
            'slug' => $slug,
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => strtoupper($slug),
            'price' => $price,
            'is_active' => true,
        ]);
    }

    private function makeCartWithTotal(float $total): Cart
    {
        $variant = new ProductVariant([
            'price' => $total,
            'is_active' => true,
        ]);
        $variant->id = 999;

        $item = new CartItem([
            'variant_id' => $variant->id,
            'quantity' => 1,
            'is_selected' => true,
        ]);
        $item->setRelation('variant', $variant);

        $cart = new Cart();
        $cart->setRelation('items', new EloquentCollection([$item]));

        return $cart;
    }
}
