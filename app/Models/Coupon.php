<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'code', 'description', 'discount_type', 'discount_value',
        'max_discount_amount', 'min_order_value', 'max_uses',
        'max_uses_per_user', 'used_count', 'start_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'discount_value'     => 'float',
        'max_discount_amount'=> 'float',
        'min_order_value'    => 'float',
        'is_active'          => 'boolean',
        'start_at'           => 'datetime',
        'expires_at'         => 'datetime',
    ];

    public function usages() { return $this->hasMany(CouponUsage::class); }
    public function orders() { return $this->hasMany(Order::class); }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->start_at   && now()->lt($this->start_at))   return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        if ($this->max_uses   && $this->used_count >= $this->max_uses) return false;
        return true;
    }
}