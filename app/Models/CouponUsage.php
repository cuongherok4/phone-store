<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    public $timestamps = false;
    protected $fillable = ['coupon_id', 'user_id', 'order_id'];

    protected $casts = ['used_at' => 'datetime'];

    public function coupon() { return $this->belongsTo(Coupon::class); }
    public function user()   { return $this->belongsTo(User::class); }
    public function order()  { return $this->belongsTo(Order::class); }
}