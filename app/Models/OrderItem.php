<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_id', 'variant_id', 'sku', 'name', 'price', 'quantity', 'subtotal'];

    protected $casts = [
        'price' => 'float',
        'subtotal' => 'float',
    ];

    public function order()   { return $this->belongsTo(Order::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function review()  { return $this->hasOne(Review::class); }

    public function getSubtotalAttribute(): float
    {
        return array_key_exists('subtotal', $this->attributes)
            ? (float) $this->attributes['subtotal']
            : $this->price * $this->quantity;
    }
}
