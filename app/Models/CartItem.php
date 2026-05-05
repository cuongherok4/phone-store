<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['cart_id', 'variant_id', 'quantity'];

    public function cart()    { return $this->belongsTo(Cart::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'variant_id'); }

    public function getSubtotalAttribute(): float
    {
        return $this->variant->price * $this->quantity;
    }
}