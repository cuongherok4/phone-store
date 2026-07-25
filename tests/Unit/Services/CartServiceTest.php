<?php

namespace Tests\Unit\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\InventoryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $service;
    private $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('variant_images');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->default('customer');
            $table->timestamps();
            $table->softDeletes();
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

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('variant_id');
            $table->integer('quantity')->default(1);
            $table->boolean('is_selected')->default(true);
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

        $this->inventoryService = Mockery::mock(InventoryService::class);
        $this->service = new CartService($this->inventoryService);
        $this->actingAs(User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'role' => 'customer',
        ]));
    }

    public function test_it_adds_a_new_cart_item_and_accumulates_existing_quantity(): void
    {
        $variant = $this->createVariant();

        $this->inventoryService
            ->shouldReceive('getStock')
            ->twice()
            ->with($variant->id)
            ->andReturn(5);

        $this->service->addItem($variant->id, 2);
        $cart = $this->service->addItem($variant->id, 1);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
            'is_selected' => true,
        ]);
        $this->assertSame(3, $cart->fresh()->load('items')->item_count);
    }

    public function test_it_rejects_add_when_requested_quantity_exceeds_stock(): void
    {
        $variant = $this->createVariant();

        $this->inventoryService
            ->shouldReceive('getStock')
            ->once()
            ->with($variant->id)
            ->andReturn(1);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Không đủ hàng trong kho. Hiện còn: 1');

        $this->service->addItem($variant->id, 2);
    }

    public function test_it_updates_quantity_and_deletes_item_when_quantity_is_zero(): void
    {
        $variant = $this->createVariant();

        $this->inventoryService
            ->shouldReceive('getStock')
            ->times(3)
            ->with($variant->id)
            ->andReturn(5);

        $cart = $this->service->addItem($variant->id, 1);
        $item = $cart->fresh()->items()->first();

        $this->service->updateItem($item->id, 4);
        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 4,
        ]);

        $this->service->updateItem($item->id, 0);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }

    public function test_it_removes_item_from_cart(): void
    {
        $variant = $this->createVariant();

        $this->inventoryService
            ->shouldReceive('getStock')
            ->once()
            ->with($variant->id)
            ->andReturn(5);

        $cart = $this->service->addItem($variant->id, 1);
        $item = $cart->fresh()->items()->first();

        $this->service->removeItem($item->id);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }

    public function test_it_toggles_single_item_and_all_items_selection(): void
    {
        $first = $this->createVariant('IP15-128');
        $second = $this->createVariant('S24-256');

        $this->inventoryService
            ->shouldReceive('getStock')
            ->twice()
            ->andReturn(5);

        $this->service->addItem($first->id, 1);
        $cart = $this->service->addItem($second->id, 1);
        $items = $cart->fresh()->items()->orderBy('id')->get();

        $this->service->toggleSelection($items[0]->id, false);
        $this->assertFalse((bool) CartItem::find($items[0]->id)->is_selected);
        $this->assertTrue((bool) CartItem::find($items[1]->id)->is_selected);

        $this->service->toggleAll(false);
        $this->assertSame(0, CartItem::where('is_selected', true)->count());

        $this->service->toggleAll(true);
        $this->assertSame(2, CartItem::where('is_selected', true)->count());
    }

    private function createVariant(string $sku = 'IP15-128'): ProductVariant
    {
        $product = Product::create([
            'name' => $sku,
            'slug' => strtolower($sku),
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => 10_000_000,
            'is_active' => true,
        ]);
    }
}
