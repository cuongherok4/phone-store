<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Services\RelatedProductService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RelatedProductServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('variant_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
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
            $table->integer('sort_order')->default(0);
        });
    }

    public function test_it_prioritizes_same_brand_products_by_quality_signals(): void
    {
        $current = $this->createProduct('Current', brandId: 1, rating: 4.0, reviews: 10);
        $this->createProduct('Same brand lower', brandId: 1, rating: 4.2, reviews: 20);
        $this->createProduct('Same brand best', brandId: 1, rating: 4.9, reviews: 50);
        $this->createProduct('Other brand', brandId: 2, rating: 5.0, reviews: 100);

        $related = app(RelatedProductService::class)->getFor($current, 2);

        $this->assertSame(['Same brand best', 'Same brand lower'], $related->pluck('name')->all());
    }

    public function test_it_fills_with_other_active_products_when_same_brand_is_not_enough(): void
    {
        $current = $this->createProduct('Current', brandId: 1, rating: 4.0, reviews: 10);
        $this->createProduct('Same brand', brandId: 1, rating: 4.2, reviews: 20);
        $this->createProduct('Other brand best', brandId: 2, rating: 5.0, reviews: 100);
        $this->createProduct('Inactive product', brandId: 2, rating: 5.0, reviews: 200, status: false);

        $related = app(RelatedProductService::class)->getFor($current, 3);

        $this->assertSame(['Same brand', 'Other brand best'], $related->pluck('name')->all());
        $this->assertFalse($related->contains('id', $current->id));
    }

    private function createProduct(
        string $name,
        int $brandId,
        float $rating,
        int $reviews,
        bool $status = true
    ): Product {
        return Product::create([
            'brand_id' => $brandId,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'status' => $status,
            'avg_rating' => $rating,
            'review_count' => $reviews,
            'created_at' => now()->subMinutes($reviews),
            'updated_at' => now()->subMinutes($reviews),
        ]);
    }
}
