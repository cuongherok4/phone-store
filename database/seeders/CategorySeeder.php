<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Danh mục cha
        $dienthoai = Category::create([
            'name'       => 'Điện thoại',
            'slug'       => 'dien-thoai',
            'sort_order' => 1,
            'is_active'  => true,
        ]);


        // Danh mục con — Điện thoại
        Category::create([
            'name'       => 'Điện thoại Android',
            'slug'       => 'dien-thoai-android',
            'parent_id'  => $dienthoai->id,
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        Category::create([
            'name'       => 'Điện thoại iPhone',
            'slug'       => 'dien-thoai-iphone',
            'parent_id'  => $dienthoai->id,
            'sort_order' => 2,
            'is_active'  => true,
        ]);

    }
}