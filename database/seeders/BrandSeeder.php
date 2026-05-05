<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Apple',   'slug' => 'apple'],
            ['name' => 'Samsung', 'slug' => 'samsung'],
            ['name' => 'Xiaomi',  'slug' => 'xiaomi'],
            ['name' => 'OPPO',    'slug' => 'oppo'],
            ['name' => 'Vivo',    'slug' => 'vivo'],
            ['name' => 'Realme',  'slug' => 'realme'],
        ];

        foreach ($brands as $brand) {
            Brand::create([
                'name'      => $brand['name'],
                'slug'      => $brand['slug'],
                'is_active' => true,
            ]);
        }
    }
}