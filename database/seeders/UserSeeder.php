<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'              => 'Admin',
            'email'             => 'admin@phonestore.vn',
            'password'          => Hash::make('password'),
            'phone'             => '0900000000',
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name'              => 'Khách hàng mẫu',
            'email'             => 'customer@phonestore.vn',
            'password'          => Hash::make('password'),
            'phone'             => '0911111111',
            'role'              => 'customer',
            'email_verified_at' => now(),
        ]);
    }
}