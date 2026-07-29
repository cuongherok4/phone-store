<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    public function user()  { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(CartItem::class); }
    public function selectedItems() { return $this->hasMany(CartItem::class)->where('is_selected', true); }

    public function getTotalAttribute(): float
    {
        if ($this->relationLoaded('items') && $this->items->every(fn ($item) => $item->relationLoaded('variant'))) {
            return (float) $this->items
                ->where('is_selected', true)
                ->sum(fn ($item) => $item->variant->price * $item->quantity);
        }

        return (float) $this->items()
            ->where('is_selected', true)
            ->join('product_variants', 'cart_items.variant_id', '=', 'product_variants.id')
            ->sum(DB::raw('cart_items.quantity * product_variants.price'));
    }

    public function getItemCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->sum('quantity');
        }

        return (int) $this->items()->sum('quantity');
    }

    public function getSelectedItemCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->where('is_selected', true)->sum('quantity');
        }

        return (int) $this->items()->where('is_selected', true)->sum('quantity');
    }
}
