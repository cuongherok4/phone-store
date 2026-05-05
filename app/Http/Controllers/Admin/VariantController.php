<?php
// app/Http/Controllers/Admin/VariantController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantRequest;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttribute;
use App\Models\VariantImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class VariantController extends Controller
{
    // Trang quản lý variants của 1 sản phẩm
    public function index(int $id)
    {
        $product    = Product::withTrashed()->with([
            'variants.variantAttributes.attributeValue.attribute',
            'variants.images',
        ])->findOrFail($id);

        $attributes = Attribute::with('values')->get();

        return view('admin.variants.index', compact('product', 'attributes'));
    }

    // Thêm variant mới
    public function store(StoreVariantRequest $request, int $id)
    {
        $product = Product::findOrFail($id);

        DB::transaction(function () use ($request, $product) {
            $variant = ProductVariant::create([
                'product_id'    => $product->id,
                'sku'           => $request->sku,
                'price'         => $request->price,
                'compare_price' => $request->compare_price,
                'cost_price'    => $request->cost_price,
                'is_active'     => $request->boolean('is_active', true),
            ]);

            // Lưu attributes
            $this->syncAttributes($variant, $request->input('attributes', []));

            // Upload ảnh
            if ($request->hasFile('images')) {
                $this->handleImageUpload($variant, $request->file('images'), $request->integer('primary_image') ?? 0);
            }
        });

        return redirect()->route('admin.products.variants.index', $product->id)
            ->with('success', 'Thêm biến thể thành công!');
    }

    // Cập nhật variant
    public function update(StoreVariantRequest $request, int $id)
    {
        $variant = ProductVariant::findOrFail($id);

        DB::transaction(function () use ($request, $variant) {
            $variant->update([
                'sku'           => $request->sku,
                'price'         => $request->price,
                'compare_price' => $request->compare_price,
                'cost_price'    => $request->cost_price,
                'is_active'     => $request->boolean('is_active', true),
            ]);

            $this->syncAttributes($variant, $request->input('attributes', []));

            if ($request->hasFile('images')) {
                $this->handleImageUpload($variant, $request->file('images'), $request->integer('primary_image') ?? 0);
            }
        });

        return redirect()->route('admin.products.variants.index', $variant->product_id)
            ->with('success', 'Cập nhật biến thể thành công!');
    }

    // Xoá variant
    public function destroy(int $id)
    {
        $variant = ProductVariant::findOrFail($id);
        $productId = $variant->product_id;

        // Xoá ảnh trên disk
        foreach ($variant->images as $img) {
            Storage::disk('public')->delete($img->image_url);
        }

        $variant->delete();

        return redirect()->route('admin.products.variants.index', $productId)
            ->with('success', 'Đã xoá biến thể.');
    }

    // Upload ảnh bổ sung
    public function uploadImages(Request $request, int $id)
    {
        $request->validate([
            'images'   => 'required|array',
            'images.*' => 'image|max:5120',
        ]);

        $variant = ProductVariant::findOrFail($id);
        $this->handleImageUpload($variant, $request->file('images'), -1); // -1 = không set primary

        return redirect()->route('admin.products.variants.index', $variant->product_id)
            ->with('success', 'Upload ảnh thành công!');
    }

    // Xoá 1 ảnh
    public function deleteImage(int $imageId)
    {
        $img = VariantImage::findOrFail($imageId);
        Storage::disk('public')->delete($img->image_url);
        $productId = $img->variant->product_id;
        $img->delete();

        return redirect()->back()->with('success', 'Đã xoá ảnh.');
    }

    // Set ảnh chính
    public function setPrimaryImage(int $imageId)
    {
        $img = VariantImage::findOrFail($imageId);

        // Bỏ primary cũ trong cùng variant
        VariantImage::where('variant_id', $img->variant_id)
            ->update(['is_primary' => false]);

        $img->update(['is_primary' => true]);

        return redirect()->back()->with('success', 'Đã đặt ảnh chính.');
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    private function syncAttributes(ProductVariant $variant, array $attributes): void
    {
        // Xoá cũ rồi insert lại
        VariantAttribute::where('variant_id', $variant->id)->delete();

        foreach ($attributes as $attributeId => $valueId) {
            if (!$valueId) continue;
            VariantAttribute::create([
                'variant_id'         => $variant->id,
                'attribute_id'       => $attributeId,
                'attribute_value_id' => $valueId,
            ]);
        }
    }

    private function handleImageUpload(ProductVariant $variant, array $files, int $primaryIndex): void
    {
        $hasPrimary = VariantImage::where('variant_id', $variant->id)
            ->where('is_primary', true)->exists();

        foreach ($files as $index => $file) {
            // Resize bằng Intervention Image
            $image = Image::read($file);
            $image->scale(width: 800); // max width 800px, giữ tỉ lệ

            $path = 'variants/' . $variant->id . '/' . uniqid() . '.webp';
            Storage::disk('public')->put($path, $image->toWebp(85));

            $setPrimary = false;
            if (!$hasPrimary) {
                if ($primaryIndex >= 0) {
                    $setPrimary = $index === $primaryIndex;
                } elseif ($index === 0) {
                    $setPrimary = true;
                }
            }

            VariantImage::create([
                'variant_id' => $variant->id,
                'image_url'  => $path,
                'is_primary' => $setPrimary,
                'sort_order' => VariantImage::where('variant_id', $variant->id)->count(),
            ]);

            if ($setPrimary) {
                $hasPrimary = true;
            }
        }
    }
}