<?php

namespace Tests\Unit\Services;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductSearchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductSearchServiceTest extends TestCase
{
    private ProductSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('short_desc')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
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

        $this->service = app(ProductSearchService::class);
    }

    public function test_it_finds_active_products_by_name_description_brand_and_sku(): void
    {
        $apple = $this->createProduct('iPhone 15 Pro Max', 'Apple', 'Titanium flagship phone', 'APL-15-PM');
        $samsung = $this->createProduct('Galaxy S24 Ultra', 'Samsung', 'AI camera phone', 'SS-S24U');
        $xiaomi = $this->createProduct('Redmi Note 13', 'Xiaomi', 'Fast charging phone', 'XM-RN13');

        $this->assertSame([$apple->id], $this->idsFor('titanium'));
        $this->assertSame([$apple->id], $this->idsFor('apple'));
        $this->assertSame([$samsung->id], $this->idsFor('SS-S24U'));
        $this->assertSame([$xiaomi->id], $this->idsFor('redmi'));
    }

    public function test_it_keeps_base_visibility_constraints(): void
    {
        $visible = $this->createProduct('Visible iPhone', 'Apple', 'Public product', 'APL-VIS');
        $this->createProduct('Hidden iPhone', 'Apple', 'Private product', 'APL-HID', status: false);

        $this->assertSame([$visible->id], $this->idsFor('iphone'));
    }

    public function test_blank_keyword_does_not_filter_the_query(): void
    {
        $first = $this->createProduct('iPhone 15', 'Apple', 'Phone', 'APL-15');
        $second = $this->createProduct('Galaxy S24', 'Samsung', 'Phone', 'SS-S24');

        $this->assertSame([$first->id, $second->id], $this->idsFor('   '));
    }

    private function idsFor(string $keyword): array
    {
        return $this->service
            ->apply(Product::query()->where('status', true)->whereNull('deleted_at')->orderBy('id'), $keyword)
            ->pluck('id')
            ->all();
    }

    private function createProduct(
        string $name,
        string $brandName,
        string $description,
        string $sku,
        bool $status = true
    ): Product {
        $brand = Brand::firstOrCreate(
            ['name' => $brandName],
            ['slug' => strtolower($brandName), 'is_active' => true]
        );

        $product = Product::create([
            'brand_id' => $brand->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'short_desc' => $description,
            'description' => $description,
            'status' => $status,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => 10000000,
            'is_active' => true,
        ]);

        return $product;
    }
}
