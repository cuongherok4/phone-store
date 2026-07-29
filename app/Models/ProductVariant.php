<?php

namespace App\Models;

use App\Services\HomepageCacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'sku', 'price', 'compare_price', 'cost_price', 'is_active', 'total_stock',
    ];

    protected $casts = [
        'price'         => 'float',
        'compare_price' => 'float',
        'cost_price'    => 'float',
        'is_active'     => 'boolean',
        'total_stock'   => 'integer',
    ];

    public function product()    { return $this->belongsTo(Product::class); }
    public function images()     { return $this->hasMany(VariantImage::class, 'variant_id')->orderBy('sort_order'); }
    public function variantAttributes() { return $this->hasMany(VariantAttribute::class, 'variant_id'); }
    public function warehouses() { return $this->belongsToMany(Warehouse::class, 'inventory', 'variant_id', 'warehouse_id')->withPivot('quantity'); }
    public function inventory()  { return $this->hasMany(Inventory::class, 'variant_id'); }
    public function cartItems()  { return $this->hasMany(CartItem::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }

    public function getPrimaryImageAttribute(): ?string
    {
        return $this->images()->where('is_primary', true)->value('image_url')
            ?? $this->images()->value('image_url');
    }

    /**
     * Dùng cột cache total_stock trong DB thay vì JOIN inventory.
     * Cột này được sync bởi InventoryService sau mỗi thao tác.
     */
    public function getTotalStockAttribute(): int
    {
        // Nếu cột cache có trong attributes (eager loaded), dùng luôn
        if (array_key_exists('total_stock', $this->attributes)) {
            return (int) $this->attributes['total_stock'];
        }
        // Fallback: query trực tiếp (cho các trường hợp chưa có cột)
        return $this->warehouses()->sum('inventory.quantity');
    }

    /**
     * Sync lại total_stock từ bảng inventory.
     * Gọi bởi InventoryService sau mỗi import/deduct/restore.
     */
    public function syncStock(): void
    {
        $total = $this->inventory()->sum('quantity');
        $this->updateQuietly(['total_stock' => $total]);
        app(HomepageCacheService::class)->flush();
    }

    public function isInStock(): bool { return $this->total_stock > 0; }

    public function scopeActive($query) { return $query->where('is_active', true)->whereNull('deleted_at'); }

    protected static function booted(): void
    {
        $flushHomepageCache = fn () => app(HomepageCacheService::class)->flush();

        static::saved($flushHomepageCache);
        static::deleted($flushHomepageCache);
        static::restored($flushHomepageCache);
    }
}
