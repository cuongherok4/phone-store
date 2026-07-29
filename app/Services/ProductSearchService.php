<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSearchService
{
    private const MAX_KEYWORD_LENGTH = 120;

    public function apply(Builder $query, ?string $keyword): Builder
    {
        $keyword = $this->normalize($keyword);

        if ($keyword === '') {
            return $query;
        }

        return $query->where(function (Builder $searchQuery) use ($keyword) {
            if ($this->supportsFullTextSearch()) {
                $searchQuery->where(function (Builder $productQuery) use ($keyword) {
                    $productQuery->whereRaw(
                        'MATCH(products.name, products.short_desc, products.description) AGAINST (? IN BOOLEAN MODE)',
                        [$this->toBooleanFullTextQuery($keyword)]
                    );

                    $this->orWherePrefixLike($productQuery, 'products.name', $keyword);
                });
            } else {
                $this->applyLikeFallback($searchQuery, $keyword);
            }

            $searchQuery
                ->orWhereHas('brand', fn (Builder $brandQuery) => $this->whereLike($brandQuery, 'name', $keyword))
                ->orWhereHas('variants', function (Builder $variantQuery) use ($keyword) {
                    $variantQuery->where('is_active', true)
                        ->whereNull('deleted_at');

                    $this->whereLike($variantQuery, 'sku', $keyword);
                });
        });
    }

    public function normalize(?string $keyword): string
    {
        return Str::of($keyword ?? '')
            ->squish()
            ->limit(self::MAX_KEYWORD_LENGTH, '')
            ->toString();
    }

    private function applyLikeFallback(Builder $query, string $keyword): void
    {
        $query->where(function (Builder $productQuery) use ($keyword) {
            $this->whereLike($productQuery, 'products.name', $keyword);
            $this->orWhereLike($productQuery, 'products.short_desc', $keyword);
            $this->orWhereLike($productQuery, 'products.description', $keyword);
        });
    }

    private function whereLike(Builder $query, string $column, string $keyword): Builder
    {
        return $query->where($column, 'like', '%' . $this->escapeLike($keyword) . '%');
    }

    private function orWhereLike(Builder $query, string $column, string $keyword): Builder
    {
        return $query->orWhere($column, 'like', '%' . $this->escapeLike($keyword) . '%');
    }

    private function orWherePrefixLike(Builder $query, string $column, string $keyword): Builder
    {
        return $query->orWhere($column, 'like', $this->escapeLike($keyword) . '%');
    }

    private function escapeLike(string $keyword): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword);
    }

    private function supportsFullTextSearch(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function toBooleanFullTextQuery(string $keyword): string
    {
        $terms = preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);

        return collect($terms)
            ->map(fn (string $term) => preg_replace('/[^\pL\pN]+/u', '', $term))
            ->filter(fn (?string $term) => filled($term) && mb_strlen($term) >= 2)
            ->map(fn (string $term) => '+' . $term . '*')
            ->implode(' ') ?: $keyword;
    }
}
