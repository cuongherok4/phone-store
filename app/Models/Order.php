<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'coupon_id',
        'subtotal', 'discount_amount', 'shipping_fee', 'total_price',
        'status', 'payment_status', 'payment_method',
        'shipping_name', 'shipping_phone', 'shipping_address',
        'note', 'cancelled_reason',
    ];

    protected $casts = [
        'subtotal'        => 'float',
        'discount_amount' => 'float',
        'shipping_fee'    => 'float',
        'total_price'     => 'float',
    ];

    public function user()          { return $this->belongsTo(User::class); }
    public function address()       { return $this->belongsTo(UserAddress::class, 'address_id'); }
    public function coupon()        { return $this->belongsTo(Coupon::class); }
    public function items()         { return $this->hasMany(OrderItem::class); }
    public function payments()      { return $this->hasMany(Payment::class); }
    public function statusHistories(){ return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at'); }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['PENDING', 'CONFIRMED']);
    }

    public static function getLabel($status): string
    {
        return match($status) {
            'PENDING'   => 'Chờ xác nhận',
            'CONFIRMED' => 'Đã xác nhận',
            'SHIPPING'  => 'Đang giao hàng',
            'COMPLETED' => 'Hoàn thành',
            'CANCELLED' => 'Đã huỷ',
            default     => $status,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getLabel($this->status);
    }
}