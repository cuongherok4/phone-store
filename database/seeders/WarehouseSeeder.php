<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::create([
            'name'      => 'Kho Hà Nội',
            'location'  => 'Hà Nội',
            'is_active' => true,
        ]);

        Warehouse::create([
            'name'      => 'Kho Hồ Chí Minh',
            'location'  => 'Hồ Chí Minh',
            'is_active' => true,
        ]);
    }
}