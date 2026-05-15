<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    public function user()  { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(CartItem::class); }
    public function selectedItems() { return $this->hasMany(CartItem::class)->where('is_selected', true); }

    public function getTotalAttribute(): float
    {
        return $this->items->where('is_selected', true)->sum(fn($item) => $item->variant->price * $item->quantity);
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getSelectedItemCountAttribute(): int
    {
        return $this->items->where('is_selected', true)->sum('quantity');
    }
}