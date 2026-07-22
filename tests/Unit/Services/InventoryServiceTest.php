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

        Schema::dropIfExists('inventory');
        Schema::create('inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('quantity')->default(0);
            $table->primary(['variant_id', 'warehouse_id']);
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

    private function seedInventory(array $rows): void
    {
        foreach ($rows as $row) {
            \DB::table('inventory')->insert($row);
        }
    }
}
