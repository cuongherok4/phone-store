<?php

namespace App\Models;

use App\Services\ProductSearchService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'brand_id',
        'description', 'short_desc', 'status', 'specifications',
        'avg_rating', 'review_count',
    ];

    protected $casts = [
        'status'       => 'integer',
        'specifications' => 'array',
        'avg_rating'   => 'float',
        'review_count' => 'integer',
    ];

    public function brand()    { return $this->belongsTo(Brand::class); }
    public function variants() { return $this->hasMany(ProductVariant::class); }
    public function reviews()  { return $this->hasMany(Review::class); }
    public function wishlistedBy() { return $this->belongsToMany(User::class, 'wishlists')->withTimestamps(); }

    public function activeVariants()
    {
        return $this->variants()->where('is_active', true)->whereNull('deleted_at');
    }

    public function getMinPriceAttribute(): ?float
    {
        return $this->activeVariants()->min('price');
    }

    public function getPrimaryImageAttribute(): ?string
    {
        return $this->variants()
            ->with(['images' => fn($q) => $q->where('is_primary', true)])
            ->first()?->images?->first()?->image_url;
    }

    /**
     * Dùng cột cache avg_rating trong DB.
     * Cột này được cập nhật bởi ReviewService::syncProductStats()
     * thay vì query AVG() mỗi lần.
     */
    public function getAvgRatingAttribute(): float
    {
        // Nếu cột cache chưa có (môi trường cũ), fallback về query
        if (array_key_exists('avg_rating', $this->attributes)) {
            return round((float) $this->attributes['avg_rating'], 1);
        }
        $avg = $this->reviews()->where('is_approved', true)->avg('rating');
        return round($avg ?? 5.0, 1);
    }

    public function getReviewCountAttribute(): int
    {
        if (array_key_exists('review_count', $this->attributes)) {
            return (int) $this->attributes['review_count'];
        }

        return $this->reviews()->where('is_approved', true)->count();
    }

    /**
     * Cập nhật cache avg_rating và review_count.
     * Gọi bởi ReviewService sau mỗi lần approve/delete review.
     */
    public function syncReviewStats(): void
    {
        $stats = $this->reviews()->where('is_approved', true)
            ->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')
            ->first();

        $this->updateQuietly([
            'avg_rating'   => round((float) ($stats->avg ?? 0), 2),
            'review_count' => (int) ($stats->cnt ?? 0),
        ]);
    }

    public function scopeActive($query)  { return $query->where('status', 1)->whereNull('deleted_at'); }
    public function scopeSearch($query, $keyword)
    {
        return app(ProductSearchService::class)->apply($query, $keyword);
    }
}
