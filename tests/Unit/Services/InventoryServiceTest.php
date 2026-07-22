<?php

namespace Tests\Unit\Services;

use App\Services\InventoryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('product_variants');

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('total_stock')->default(0);
            $table->softDeletes();
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('quantity')->default(0);
            $table->primary(['variant_id', 'warehouse_id']);
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('change_type');
            $table->integer('quantity_change');
            $table->integer('quantity_before')->nullable();
            $table->integer('quantity_after')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    public function test_it_allows_checkout_when_grouped_stock_is_available_across_warehouses(): void
    {
        $this->seedInventory([
            ['variant_id' => 1, 'warehouse_id' => 1, 'quantity' => 2],
            ['variant_id' => 1, 'warehouse_id' => 2, 'quantity' => 3],
            ['variant_id' => 2, 'warehouse_id' => 1, 'quantity' => 1],
        ]);

        app(InventoryService::class)->assertManyAvailable([
            ['variant_id' => 1, 'quantity' => 4, 'name' => 'iPhone 15'],
            ['variant_id' => 2, 'quantity' => 1, 'name' => 'Galaxy S24'],
        ], true);

        $this->assertTrue(true);
    }

    public function test_it_groups_duplicate_variant_requirements_before_checking_stock(): void
    {
        $this->seedInventory([
            ['variant_id' => 1, 'warehouse_id' => 1, 'quantity' => 5],
        ]);

        app(InventoryService::class)->assertManyAvailable([
            ['variant_id' => 1, 'quantity' => 2, 'name' => 'iPhone 15'],
            ['variant_id' => 1, 'quantity' => 3, 'name' => 'iPhone 15'],
        ], true);

        $this->assertTrue(true);
    }

    public function test_it_rejects_checkout_when_stock_is_not_enough(): void
    {
        $this->seedInventory([
            ['variant_id' => 1, 'warehouse_id' => 1, 'quantity' => 2],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('hiện chỉ còn 2 sản phẩm');

        app(InventoryService::class)->assertManyAvailable([
            ['variant_id' => 1, 'quantity' => 3, 'name' => 'iPhone 15'],
        ], true);
    }

    public function test_it_restores_cancelled_order_stock_with_return_log_type(): void
    {
        $this->seedInventory([
            ['variant_id' => 1, 'warehouse_id' => 1, 'quantity' => 2],
        ]);

        app(InventoryService::class)->restore(1, 3, 99);

        $this->assertDatabaseHas('inventory', [
            'variant_id' => 1,
            'warehouse_id' => 1,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('inventory_logs', [
            'variant_id' => 1,
            'warehouse_id' => 1,
            'change_type' => 'RETURN',
            'quantity_change' => 3,
            'quantity_before' => 2,
            'quantity_after' => 5,
            'reference_type' => 'order',
            'reference_id' => 99,
        ]);
    }

    private function seedInventory(array $rows): void
    {
        foreach ($rows as $row) {
            \DB::table('inventory')->insert($row);
        }
    }
}
