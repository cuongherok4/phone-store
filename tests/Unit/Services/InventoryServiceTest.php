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
            $table->timestamps();
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
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('change_type');
            $table->integer('quantity_change');
            $table->integer('quantity_before')->nullable();
            $table->integer('quantity_after')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->decimal('import_price', 12, 2)->nullable();
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

    public function test_it_imports_stock_creates_inventory_log_and_syncs_variant_total_stock(): void
    {
        $variantId = $this->createVariant();

        app(InventoryService::class)->import(
            variantId: $variantId,
            warehouseId: 1,
            qty: 5,
            note: 'Nhập lô đầu',
            adminId: 9,
            supplierId: 7,
            importPrice: 12_000_000
        );

        $this->assertDatabaseHas('inventory', [
            'variant_id' => $variantId,
            'warehouse_id' => 1,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('inventory_logs', [
            'variant_id' => $variantId,
            'warehouse_id' => 1,
            'supplier_id' => 7,
            'change_type' => 'IMPORT',
            'quantity_change' => 5,
            'quantity_before' => 0,
            'quantity_after' => 5,
            'created_by' => 9,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variantId,
            'total_stock' => 5,
        ]);
    }

    public function test_it_rejects_import_with_non_positive_quantity(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('lớn hơn 0');

        app(InventoryService::class)->import(1, 1, 0, 'Sai số lượng', 9);
    }

    public function test_it_deducts_stock_across_warehouses_and_logs_exports(): void
    {
        $variantId = $this->createVariant();
        $this->seedInventory([
            ['variant_id' => $variantId, 'warehouse_id' => 1, 'quantity' => 3],
            ['variant_id' => $variantId, 'warehouse_id' => 2, 'quantity' => 5],
        ]);

        app(InventoryService::class)->deduct($variantId, 6, 101);

        $this->assertDatabaseHas('inventory', [
            'variant_id' => $variantId,
            'warehouse_id' => 2,
            'quantity' => 0,
        ]);
        $this->assertDatabaseHas('inventory', [
            'variant_id' => $variantId,
            'warehouse_id' => 1,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('inventory_logs', [
            'variant_id' => $variantId,
            'warehouse_id' => 2,
            'change_type' => 'EXPORT',
            'quantity_change' => -5,
            'reference_type' => 'order',
            'reference_id' => 101,
        ]);
        $this->assertDatabaseHas('inventory_logs', [
            'variant_id' => $variantId,
            'warehouse_id' => 1,
            'change_type' => 'EXPORT',
            'quantity_change' => -1,
            'reference_type' => 'order',
            'reference_id' => 101,
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variantId,
            'total_stock' => 2,
        ]);
    }

    public function test_it_rolls_back_deduct_when_stock_is_not_enough(): void
    {
        $variantId = $this->createVariant();
        $this->seedInventory([
            ['variant_id' => $variantId, 'warehouse_id' => 1, 'quantity' => 2],
        ]);

        try {
            app(InventoryService::class)->deduct($variantId, 3, 101);
            $this->fail('Expected insufficient stock exception.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Không đủ', $e->getMessage());
        }

        $this->assertDatabaseHas('inventory', [
            'variant_id' => $variantId,
            'warehouse_id' => 1,
            'quantity' => 2,
        ]);
        $this->assertDatabaseCount('inventory_logs', 0);
    }

    public function test_it_adjusts_existing_stock_and_logs_difference(): void
    {
        $variantId = $this->createVariant();
        $this->seedInventory([
            ['variant_id' => $variantId, 'warehouse_id' => 1, 'quantity' => 2],
        ]);

        app(InventoryService::class)->adjust($variantId, 1, 8, 'Kiểm kho cuối ngày', 9);

        $this->assertDatabaseHas('inventory', [
            'variant_id' => $variantId,
            'warehouse_id' => 1,
            'quantity' => 8,
        ]);
        $this->assertDatabaseHas('inventory_logs', [
            'variant_id' => $variantId,
            'warehouse_id' => 1,
            'change_type' => 'ADJUST',
            'quantity_change' => 6,
            'quantity_before' => 2,
            'quantity_after' => 8,
            'created_by' => 9,
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variantId,
            'total_stock' => 8,
        ]);
    }

    public function test_it_does_not_log_adjust_when_quantity_is_unchanged(): void
    {
        $variantId = $this->createVariant();
        $this->seedInventory([
            ['variant_id' => $variantId, 'warehouse_id' => 1, 'quantity' => 2],
        ]);

        app(InventoryService::class)->adjust($variantId, 1, 2, 'Không đổi', 9);

        $this->assertDatabaseCount('inventory_logs', 0);
    }

    public function test_it_rejects_negative_adjustment(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('không thể âm');

        app(InventoryService::class)->adjust(1, 1, -1, 'Sai số lượng', 9);
    }

    private function createVariant(): int
    {
        return \DB::table('product_variants')->insertGetId([
            'total_stock' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedInventory(array $rows): void
    {
        foreach ($rows as $row) {
            \DB::table('inventory')->insert($row);
        }
    }
}
