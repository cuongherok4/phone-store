<?php

namespace Tests\Unit\Services;

use App\Models\Banner;
use App\Models\Product;
use App\Services\HomepageCacheService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomepageCacheServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Schema::dropIfExists('variant_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('banners');

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
            $table->boolean('status')->default(true);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku');
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('total_stock')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('variant_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->string('image_url');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image_url');
            $table->string('link_url')->nullable();
            $table->string('type');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_homepage_products_are_cached_until_flushed(): void
    {
        $service = app(HomepageCacheService::class);
        $brandId = $this->createBrand('Apple');
        $oldProductId = $this->createProduct($brandId, 'iPhone 15', now()->subDay());

        $this->createVariant($oldProductId, 'IP15');

        $this->assertSame('iPhone 15', $service->newProducts()->first()->name);

        $newProductId = $this->createProduct($brandId, 'iPhone 16', now());
        $this->createVariant($newProductId, 'IP16');

        $this->assertSame('iPhone 15', $service->newProducts()->first()->name);

        $service->flush();

        $this->assertSame('iPhone 16', $service->newProducts()->first()->name);
    }

    public function test_homepage_banners_are_cached_until_flushed(): void
    {
        $service = app(HomepageCacheService::class);

        \DB::table('banners')->insert([
            'title' => 'Old banner',
            'image_url' => 'old.webp',
            'type' => 'MAIN',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('Old banner', $service->mainBanners()->first()->title);

        \DB::table('banners')->where('title', 'Old banner')->update(['title' => 'New banner']);

        $this->assertSame('Old banner', $service->mainBanners()->first()->title);

        $service->flush();

        $this->assertSame('New banner', $service->mainBanners()->first()->title);
    }

    public function test_product_model_changes_flush_homepage_cache(): void
    {
        $service = app(HomepageCacheService::class);
        $brandId = $this->createBrand('Apple');
        $productId = $this->createProduct($brandId, 'iPhone 15', now());

        $this->createVariant($productId, 'IP15');

        $this->assertSame('iPhone 15', $service->newProducts()->first()->name);

        Product::findOrFail($productId)->update(['name' => 'iPhone 15 Pro']);

        $this->assertSame('iPhone 15 Pro', $service->newProducts()->first()->name);
    }

    public function test_banner_model_changes_flush_homepage_cache(): void
    {
        $service = app(HomepageCacheService::class);

        $bannerId = \DB::table('banners')->insertGetId([
            'title' => 'Old banner',
            'image_url' => 'old.webp',
            'type' => 'MAIN',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('Old banner', $service->mainBanners()->first()->title);

        Banner::findOrFail($bannerId)->update(['title' => 'New banner']);

        $this->assertSame('New banner', $service->mainBanners()->first()->title);
    }

    private function createBrand(string $name): int
    {
        return \DB::table('brands')->insertGetId([
            'name' => $name,
            'slug' => strtolower($name),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProduct(int $brandId, string $name, $createdAt): int
    {
        return \DB::table('products')->insertGetId([
            'brand_id' => $brandId,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'status' => true,
            'avg_rating' => 5,
            'review_count' => 1,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function createVariant(int $productId, string $sku): void
    {
        $variantId = \DB::table('product_variants')->insertGetId([
            'product_id' => $productId,
            'sku' => $sku,
            'price' => 1000000,
            'is_active' => true,
            'total_stock' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('variant_images')->insert([
            'variant_id' => $variantId,
            'image_url' => $sku . '.webp',
            'is_primary' => true,
            'sort_order' => 1,
        ]);
    }
}
