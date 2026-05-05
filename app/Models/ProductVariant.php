<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'sku', 'price', 'compare_price', 'cost_price', 'is_active',
    ];

    protected $casts = [
        'price'         => 'float',
        'compare_price' => 'float',
        'cost_price'    => 'float',
        'is_active'     => 'boolean',
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

    public function getTotalStockAttribute(): int
    {
        return $this->warehouses()->sum('inventory.quantity');
    }

    public function isInStock(): bool { return $this->total_stock > 0; }

    public function scopeActive($query) { return $query->where('is_active', true)->whereNull('deleted_at'); }
}