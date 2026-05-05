<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ─── Danh sách sản phẩm (với filter, sort, paginate) ────────────────────────
    public function index(Request $request)
    {
        $query = Product::where('status', 1)->whereNull('deleted_at');

        // Tìm kiếm theo từ khóa
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->input('q') . '%');
        }

        // Lọc theo Thương hiệu (comma-separated slugs)
        $currentBrands = collect();
        if ($request->filled('brand')) {
            $brandSlugs   = explode(',', $request->input('brand'));
            $currentBrands = Brand::whereIn('slug', $brandSlugs)->get();
            if ($currentBrands->isNotEmpty()) {
                $query->whereIn('brand_id', $currentBrands->pluck('id'));
            }
        }

        // Lọc theo Khoảng giá
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $minPrice = (int) $request->input('min_price', 0);
            $maxPrice = (int) $request->input('max_price', 999999999);

            $query->whereHas('variants', function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice])
                  ->where('is_active', true)
                  ->whereNull('deleted_at');
            });
        }

        // Sắp xếp
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->orderBy(
                ProductVariant::select('price')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)->whereNull('deleted_at')
                    ->orderBy('price', 'asc')->limit(1),
                'asc'
            ),
            'price_desc' => $query->orderBy(
                ProductVariant::select('price')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)->whereNull('deleted_at')
                    ->orderBy('price', 'desc')->limit(1),
                'desc'
            ),
            default      => $query->orderBy('created_at', 'desc'),
        };

        // Phân trang — eager load đầy đủ tránh N+1
        $products = $query
            ->with([
                'brand',
                'variants' => function ($q) {
                    $q->where('is_active', true)
                      ->whereNull('deleted_at')
                      ->orderBy('price', 'asc')
                      ->with(['images' => fn ($imgQ) => $imgQ->orderBy('sort_order')]);
                },
            ])
            ->withAvg(['reviews as avg_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews as review_count' => fn ($q) => $q->where('is_approved', true)])
            ->paginate(12)
            ->withQueryString();

        // Sidebar filter data
        $allBrands     = Brand::where('is_active', true)->orderBy('name')->get();

        // Tiêu đề trang động
        $pageTitle = 'Tất cả sản phẩm';
        if ($request->filled('q')) {
            $pageTitle = 'Tìm kiếm: "' . $request->input('q') . '"';
        } elseif ($currentBrands->isNotEmpty()) {
            $pageTitle = $currentBrands->pluck('name')->implode(', ');
        }

        return view('customer.products.index', compact(
            'products', 'allBrands', 'pageTitle'
        ));
    }

    // ─── Chi tiết sản phẩm ───────────────────────────────────────────────────────
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->with([
                'brand',
                'variants' => function ($q) {
                    $q->where('is_active', true)
                      ->whereNull('deleted_at')
                      ->with([
                          'images'            => fn ($imgQ) => $imgQ->orderBy('sort_order'),
                          'variantAttributes.attribute',
                          'variantAttributes.attributeValue',
                      ]);
                },
                'reviews' => function ($q) {
                    $q->where('is_approved', true)->with('user')->latest();
                },
            ])
            ->firstOrFail();

        // Xây dựng dữ liệu JSON cho Alpine.js
        $attributesData = [];
        $variantsData   = [];

        // Helper: convert đường dẫn tương đối → full URL (robust)
        $toUrl = function (?string $path) use ($product): string {
            if (!$path) {
                // Fallback to placeholder if no path
                return 'https://placehold.co/600x600/EBF4FF/1E3A8A?text=' . urlencode(mb_substr($product->name, 0, 20));
            }
            if (str_starts_with($path, 'http')) return $path;
            
            // Xử lý trường hợp path đã có storage/ hoặc bắt đầu bằng /
            $cleanPath = ltrim($path, '/');
            if (str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = substr($cleanPath, 8);
            }
            
            return asset('storage/' . $cleanPath);
        };

        // Lấy ảnh đại diện chung của sản phẩm (ảnh của variant đầu tiên có ảnh)
        $productPrimaryImage = null;
        foreach ($product->variants as $v) {
            $path = $v->images->where('is_primary', true)->first()?->image_url ?? $v->images->first()?->image_url;
            if ($path) {
                $productPrimaryImage = $toUrl($path);
                break;
            }
        }
        if (!$productPrimaryImage) {
            $productPrimaryImage = $toUrl(null);
        }

        foreach ($product->variants as $variant) {
            $primaryPath = $variant->images->where('is_primary', true)->first()?->image_url
                        ?? $variant->images->first()?->image_url;

            $variantInfo = [
                'id'                      => $variant->id,
                'sku'                     => $variant->sku,
                'price'                   => $variant->price,
                'formatted_price'         => number_format($variant->price, 0, ',', '.') . 'đ',
                'compare_price'           => $variant->compare_price,
                'formatted_compare_price' => $variant->compare_price
                    ? number_format($variant->compare_price, 0, ',', '.') . 'đ'
                    : null,
                'stock'                   => $variant->total_stock,
                // Convert sang full URL trước khi encode JSON
                'images'                  => $variant->images
                    ->map(fn ($img) => $toUrl($img->image_url))
                    ->filter()
                    ->values()
                    ->toArray(),
                'primary_image'           => $primaryPath ? $toUrl($primaryPath) : null,
                'attributes'              => [],
            ];

            foreach ($variant->variantAttributes as $va) {
                $attrName  = $va->attribute->name;
                $attrValue = $va->attributeValue->value;

                $variantInfo['attributes'][$attrName] = $attrValue;

                if (! isset($attributesData[$attrName])) {
                    $attributesData[$attrName] = [];
                }
                if (! in_array($attrValue, $attributesData[$attrName])) {
                    $attributesData[$attrName][] = $attrValue;
                }
            }

            $variantsData[] = $variantInfo;
        }

        // Sản phẩm liên quan (loại trừ sản phẩm hiện tại)
        $relatedProducts = Product::where('status', 1)
            ->whereNull('deleted_at')
            ->where('id', '!=', $product->id)
            ->with([
                'brand',
                'variants' => fn ($q) => $q->where('is_active', true)
                    ->orderBy('price', 'asc')
                    ->with(['images' => fn ($imgQ) => $imgQ->orderBy('sort_order')]),
            ])
            ->withAvg(['reviews as avg_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews as review_count' => fn ($q) => $q->where('is_approved', true)])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Kiểm tra xem user có thể đánh giá sản phẩm này không
        $canReview = false;
        $orderItemToReview = null;
        if (auth()->check()) {
            $orderItemToReview = \App\Models\OrderItem::whereHas('order', function($q) {
                    $q->where('user_id', auth()->id())->where('status', 'COMPLETED');
                })
                ->whereHas('variant', function($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->whereDoesntHave('review')
                ->first();
            
            $canReview = (bool)$orderItemToReview;
        }

        return view('customer.products.show', compact(
            'product', 'attributesData', 'variantsData', 'relatedProducts', 'productPrimaryImage', 'canReview', 'orderItemToReview'
        ));
    }

    // ─── Redirect theo Thương hiệu ────────────────────────────────────────────────
    public function byBrand($slug)
    {
        $brand = Brand::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return redirect()->route('customer.products.index', ['brand' => $brand->slug]);
    }
}
