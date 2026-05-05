<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_id', 'variant_id', 'sku', 'name', 'price', 'quantity'];

    protected $casts = ['price' => 'float'];

    public function order()   { return $this->belongsTo(Order::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function review()  { return $this->hasOne(Review::class); }

    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}