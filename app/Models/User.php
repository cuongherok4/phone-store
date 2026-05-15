<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'deleted_at'        => 'datetime',
    ];

    // ===================== RELATIONSHIPS =====================
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function wishlists()
    {
        return $this->belongsToMany(Product::class, 'wishlists')
                    ->withPivot('created_at');
    }

    // ===================== HELPERS =====================
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Lấy địa chỉ mặc định, nếu không có thì lấy địa chỉ mới nhất
     */
    public function defaultAddress()
    {
        return $this->addresses()
                    ->where('is_default', true)
                    ->first()
            ?? $this->addresses()->latest()->first();
    }

    /**
     * Redirect route sau khi login
     */
    public function getRedirectRoute(): string
    {
        return $this->isAdmin() ? route('admin.dashboard') : route('home');
    }

    
}