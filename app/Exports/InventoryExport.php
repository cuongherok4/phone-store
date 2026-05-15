<?php

namespace App\Exports;

use App\Models\ProductVariant;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function query()
    {
        return ProductVariant::query()
            ->with(['product', 'variantAttributes.attributeValue'])
            ->orderBy('stock', 'asc'); // Ưu tiên hàng sắp hết lên đầu
    }

    public function headings(): array
    {
        return [
            'Sản Phẩm',
            'Phiên Bản (Biến thể)',
            'SKU',
            'Tồn Kho Hiện Tại',
            'Giá Nhập (VNĐ)',
            'Giá Bán (VNĐ)',
            'Tình Trạng',
        ];
    }

    public function map($variant): array
    {
        // Lấy tên biến thể (RAM/ROM/Color)
        $attributes = $variant->variantAttributes->map(function($va) {
            return $va->attributeValue->value;
        })->implode(' / ');

        return [
            $variant->product->name,
            $attributes,
            $variant->sku,
            $variant->stock,
            number_format($variant->import_price ?? 0, 0, ',', '.'),
            number_format($variant->price, 0, ',', '.'),
            $variant->stock <= 5 ? 'Sắp hết hàng' : ($variant->stock == 0 ? 'Hết hàng' : 'Còn hàng'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
