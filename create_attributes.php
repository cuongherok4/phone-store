<?php
$ram = \App\Models\Attribute::create(['name' => 'RAM', 'code' => 'ram']);
$rom = \App\Models\Attribute::create(['name' => 'ROM', 'code' => 'rom']);

// RAM values
\App\Models\AttributeValue::create(['attribute_id' => $ram->id, 'value' => '8GB', 'numeric_value' => 8, 'sort_order' => 1]);
\App\Models\AttributeValue::create(['attribute_id' => $ram->id, 'value' => '12GB', 'numeric_value' => 12, 'sort_order' => 2]);
\App\Models\AttributeValue::create(['attribute_id' => $ram->id, 'value' => '16GB', 'numeric_value' => 16, 'sort_order' => 3]);

// ROM values
\App\Models\AttributeValue::create(['attribute_id' => $rom->id, 'value' => '256GB', 'numeric_value' => 256, 'sort_order' => 1]);
\App\Models\AttributeValue::create(['attribute_id' => $rom->id, 'value' => '512GB', 'numeric_value' => 512, 'sort_order' => 2]);
\App\Models\AttributeValue::create(['attribute_id' => $rom->id, 'value' => '1TB', 'numeric_value' => 1024, 'sort_order' => 3]);

echo "✅ Tạo 2 attributes mới: RAM, ROM\n";
echo "Tổng attributes hiện có: " . \App\Models\Attribute::count() . "\n";
