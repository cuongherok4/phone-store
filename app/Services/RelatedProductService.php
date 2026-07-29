<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class RelatedProductService
{
    public function getFor(Product $product, int $limit = 4): Collection
    {
        $selected = collect();

        if ($product->brand_id) {
            $selected = $this->baseQuery($product)
                ->where('brand_id', $product->brand_id)
                ->limit($limit)
                ->get();
        }

        if ($selected->count() < $limit) {
            $fallback = $this->baseQuery($product)
                ->whereNotIn('id', $selected->pluck('id')->push($product->id)->all())
                ->limit($limit - $selected->count())
                ->get();

            $selected = $selected->merge($fallback);
        }

        return $selected->values();
    }

    private function baseQuery(Product $product)
    {
        return Product::where('status', 1)
            ->whereNull('deleted_at')
            ->where('id', '!=', $product->id)
            ->with([
                'brand',
                'variants' => fn ($q) => $q->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->orderBy('price', 'asc')
                    ->with(['images' => fn ($imgQ) => $imgQ->orderBy('sort_order')]),
            ])
            ->orderByDesc('avg_rating')
            ->orderByDesc('review_count')
            ->orderByDesc('created_at');
    }
}
