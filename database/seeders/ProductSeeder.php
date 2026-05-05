<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttribute;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo attributes
        $color = Attribute::create(['name' => 'Màu sắc', 'code' => 'color']);
        $storage = Attribute::create(['name' => 'Dung lượng', 'code' => 'storage']);

        // Attribute values — màu
        $den   = AttributeValue::create(['attribute_id' => $color->id,   'value' => 'Đen',  'color_hex' => '#000000', 'sort_order' => 1]);
        $trang = AttributeValue::create(['attribute_id' => $color->id,   'value' => 'Trắng','color_hex' => '#FFFFFF', 'sort_order' => 2]);
        $xanh  = AttributeValue::create(['attribute_id' => $color->id,   'value' => 'Xanh', 'color_hex' => '#0000FF', 'sort_order' => 3]);

        // Attribute values — dung lượng
        $g128  = AttributeValue::create(['attribute_id' => $storage->id, 'value' => '128GB', 'numeric_value' => 128, 'sort_order' => 1]);
        $g256  = AttributeValue::create(['attribute_id' => $storage->id, 'value' => '256GB', 'numeric_value' => 256, 'sort_order' => 2]);

        $apple    = Brand::where('slug', 'apple')->first();
        $samsung  = Brand::where('slug', 'samsung')->first();
        $iphone   = Category::where('slug', 'dien-thoai-iphone')->first();
        $android  = Category::where('slug', 'dien-thoai-android')->first();
        $warehouse = Warehouse::first();

        // ── Sản phẩm 1: iPhone 15 ──
        $iphone15 = Product::create([
            'name'        => 'iPhone 15',
            'slug'        => 'iphone-15',
            'brand_id'    => $apple->id,
            'category_id' => $iphone->id,
            'short_desc'  => 'iPhone 15 chip A16 Bionic, camera 48MP',
            'description' => 'iPhone 15 với chip A16 Bionic mạnh mẽ, camera chính 48MP, cổng USB-C.',
            'status'      => 1,
        ]);

        // Variants iPhone 15
        $variants15 = [
            ['sku' => 'IP15-DEN-128', 'price' => 19990000, 'compare_price' => 21990000, 'color' => $den,   'storage' => $g128],
            ['sku' => 'IP15-DEN-256', 'price' => 22990000, 'compare_price' => 24990000, 'color' => $den,   'storage' => $g256],
            ['sku' => 'IP15-TRG-128', 'price' => 19990000, 'compare_price' => 21990000, 'color' => $trang, 'storage' => $g128],
            ['sku' => 'IP15-TRG-256', 'price' => 22990000, 'compare_price' => 24990000, 'color' => $trang, 'storage' => $g256],
        ];

        foreach ($variants15 as $v) {
            $variant = ProductVariant::create([
                'product_id'    => $iphone15->id,
                'sku'           => $v['sku'],
                'price'         => $v['price'],
                'compare_price' => $v['compare_price'],
                'is_active'     => true,
            ]);
            VariantAttribute::create(['variant_id' => $variant->id, 'attribute_id' => $color->id,   'attribute_value_id' => $v['color']->id]);
            VariantAttribute::create(['variant_id' => $variant->id, 'attribute_id' => $storage->id, 'attribute_value_id' => $v['storage']->id]);
            Inventory::create(['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id, 'quantity' => 50]);
        }

        // ── Sản phẩm 2: Samsung Galaxy S24 ──
        $s24 = Product::create([
            'name'        => 'Samsung Galaxy S24',
            'slug'        => 'samsung-galaxy-s24',
            'brand_id'    => $samsung->id,
            'category_id' => $android->id,
            'short_desc'  => 'Galaxy S24 chip Snapdragon 8 Gen 3, màn hình 6.2 inch',
            'description' => 'Samsung Galaxy S24 với Snapdragon 8 Gen 3, camera 50MP, pin 4000mAh.',
            'status'      => 1,
        ]);

        $variantsS24 = [
            ['sku' => 'S24-DEN-128', 'price' => 17990000, 'compare_price' => 19990000, 'color' => $den,  'storage' => $g128],
            ['sku' => 'S24-XNH-256', 'price' => 20990000, 'compare_price' => 22990000, 'color' => $xanh, 'storage' => $g256],
        ];

        foreach ($variantsS24 as $v) {
            $variant = ProductVariant::create([
                'product_id'    => $s24->id,
                'sku'           => $v['sku'],
                'price'         => $v['price'],
                'compare_price' => $v['compare_price'],
                'is_active'     => true,
            ]);
            VariantAttribute::create(['variant_id' => $variant->id, 'attribute_id' => $color->id,   'attribute_value_id' => $v['color']->id]);
            VariantAttribute::create(['variant_id' => $variant->id, 'attribute_id' => $storage->id, 'attribute_value_id' => $v['storage']->id]);
            Inventory::create(['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id, 'quantity' => 30]);
        }
    }
}