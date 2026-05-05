<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id', 'receiver_name', 'receiver_phone',
        'province', 'district', 'ward', 'address_detail', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_detail, $this->ward, $this->district, $this->province
        ]));
    }
}