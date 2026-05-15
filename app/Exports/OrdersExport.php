<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $status;

    public function __construct($startDate = null, $endDate = null, $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    public function query()
    {
        $query = Order::query()->with('user');

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Mã Đơn Hàng',
            'Khách Hàng',
            'Số Điện Thoại',
            'Địa Chỉ',
            'Tổng Tiền (VNĐ)',
            'Trạng Thái',
            'Thanh Toán',
            'Ngày Đặt',
        ];
    }

    public function map($order): array
    {
        return [
            '#' . $order->id,
            $order->shipping_name ?? ($order->user->name ?? 'N/A'),
            $order->shipping_phone,
            $order->shipping_address,
            number_format($order->total_price, 0, ',', '.'),
            $this->formatStatus($order->status),
            $order->payment_method,
            $order->created_at->format('d/m/Y H:i'),
        ];
    }

    protected function formatStatus($status)
    {
        return match($status) {
            'PENDING'   => 'Chờ xử lý',
            'CONFIRMED' => 'Đã xác nhận',
            'SHIPPING'  => 'Đang giao',
            'DELIVERED' => 'Đã giao',
            'CANCELLED' => 'Đã hủy',
            default     => $status,
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
