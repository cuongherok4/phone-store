<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id', 'user_id', 'order_item_id', 'rating', 'comment', 'images', 'is_approved'];

    protected $casts = [
        'images'      => 'array',
        'is_approved' => 'boolean',
        'created_at'  => 'datetime',
    ];

    public function product()   { return $this->belongsTo(Product::class); }
    public function user()      { return $this->belongsTo(User::class); }
    public function orderItem() { return $this->belongsTo(OrderItem::class); }
}