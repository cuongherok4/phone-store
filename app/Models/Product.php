<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'brand_id',
        'description', 'short_desc', 'status', 'specifications'
    ];

    protected $casts = [
        'status' => 'integer',
        'specifications' => 'array'
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

    public function getAvgRatingAttribute(): float
    {
        $avg = $this->reviews()->where('is_approved', true)->avg('rating');
        return round($avg ?? 5.0, 1);
    }

    public function scopeActive($query)  { return $query->where('status', 1)->whereNull('deleted_at'); }
    public function scopeSearch($query, $keyword)
    {
        return $query->where('name', 'like', "%{$keyword}%");
    }
}