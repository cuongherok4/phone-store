<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Hoá đơn #{{ $order->id }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url(http://tweb.com.vn/fonts/DejaVuSans.ttf) format('truetype');
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .invoice-container {
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-logo {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .company-info {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .invoice-title {
            text-align: right;
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }
        .info-table {
            width: 100%;
            margin-bottom: 40px;
        }
        .info-box {
            vertical-align: top;
            width: 50%;
        }
        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #9ca3af;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .info-content {
            font-size: 13px;
            color: #374151;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .product-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
        }
        .product-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .row-even {
            background-color: #fdfdfd;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .totals-table {
            width: 100%;
        }
        .totals-box {
            width: 40%;
            margin-left: 60%;
        }
        .total-row {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .grand-total-row {
            padding: 15px 0;
            color: #2563eb;
            font-size: 18px;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 60px;
            width: 100%;
        }
        .signature-box {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 80px;
        }
        .footer {
            margin-top: 80px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            color: #94a3b8;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        {{-- Header Section --}}
        <table class="header-table">
            <tr>
                <td>
                    <div class="company-logo">PhoneStore</div>
                    <div class="company-info">
                        {{ $settings['store_address'] ?? 'Địa chỉ đang cập nhật' }}<br>
                        Hotline: {{ $settings['store_phone'] ?? '1900 xxxx' }} | Email: {{ $settings['store_email'] ?? 'contact@phonestore.vn' }}
                    </div>
                </td>
                <td class="text-right">
                    <div class="invoice-title">HOÁ ĐƠN GIÁ TRỊ GIA TĂNG</div>
                    <div style="margin-top: 5px; color: #64748b;">
                        Mã đơn: <span class="font-bold">#{{ $order->id }}</span><br>
                        Ngày lập: {{ now()->format('d/m/Y') }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Info Section --}}
        <table class="info-table">
            <tr>
                <td class="info-box">
                    <span class="info-label">Thông tin khách hàng</span>
                    <div class="info-content">
                        <span class="font-bold">{{ $order->shipping_name }}</span><br>
                        Số ĐT: {{ $order->shipping_phone }}<br>
                        Địa chỉ: {{ $order->shipping_address }}
                    </div>
                </td>
                <td class="info-box" style="padding-left: 40px;">
                    <span class="info-label">Thông tin thanh toán</span>
                    <div class="info-content">
                        Phương thức: {{ $order->payment_method }}<br>
                        Trạng thái: <span class="font-bold">{{ \App\Models\Order::getLabel($order->status) }}</span><br>
                        @if($order->note) Ghi chú: {{ $order->note }} @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Product Table --}}
        <table class="product-table">
            <thead>
                <tr>
                    <th width="40%">Sản phẩm</th>
                    <th width="20%">SKU</th>
                    <th class="text-center" width="10%">SL</th>
                    <th class="text-right" width="15%">Đơn giá</th>
                    <th class="text-right" width="15%">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                    <tr class="{{ $index % 2 == 0 ? '' : 'row-even' }}">
                        <td>
                            <div class="font-bold">{{ $item->variant->product->name }}</div>
                            <div style="font-size: 10px; color: #666;">
                                {{ $item->variant->variantAttributes->map(fn($va) => $va->attributeValue->value)->implode(' / ') }}
                            </div>
                        </td>
                        <td style="color: #64748b;">{{ $item->sku }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price, 0, ',', '.') }}đ</td>
                        <td class="text-right font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary Section --}}
        <table class="totals-table">
            <tr>
                <td width="60%">
                    <div style="padding: 20px; background-color: #f8fafc; border-radius: 10px; font-size: 11px; color: #64748b;">
                        <span class="font-bold" style="color: #475569;">Ghi chú thanh toán:</span><br>
                        - Quý khách vui lòng kiểm tra kỹ hàng trước khi ký nhận.<br>
                        - Hoá đơn này có giá trị thay thế phiếu bảo hành.<br>
                        - Liên hệ Hotline nếu cần hỗ trợ kỹ thuật hoặc đổi trả.
                    </div>
                </td>
                <td width="40%" style="padding-left: 20px;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 5px 0; color: #64748b;">Tạm tính:</td>
                            <td class="text-right" style="padding: 5px 0;">{{ number_format($order->subtotal, 0, ',', '.') }}đ</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0; color: #64748b;">Phí vận chuyển:</td>
                            <td class="text-right" style="padding: 5px 0;">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                        </tr>
                        @if($order->discount_amount > 0)
                            <tr>
                                <td style="padding: 5px 0; color: #ef4444;">Giảm giá:</td>
                                <td class="text-right" style="padding: 5px 0; color: #ef4444;">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="2" style="border-top: 2px solid #e2e8f0; margin: 10px 0;"></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; font-size: 16px; font-weight: bold; color: #1f2937;">Tổng cộng:</td>
                            <td class="text-right" style="padding: 10px 0; font-size: 20px; font-weight: bold; color: #2563eb;">{{ number_format($order->total_price, 0, ',', '.') }}đ</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Signature Section --}}
        <table class="signature-section">
            <tr>
                <td width="60%"></td>
                <td class="signature-box" style="width: 40%;">
                    <span class="font-bold">Người lập hóa đơn</span><br>
                    <span style="font-size: 10px; color: #94a3b8;">(Ký, đóng dấu)</span>
                    <div class="signature-space"></div>
                    <div class="font-bold">{{ Auth::user()->name }}</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>Cảm ơn quý khách đã tin tưởng và đồng hành cùng PhoneStore!<br>
            www.phonestore.vn - Hotline: 1900 xxxx</p>
        </div>
    </div>
</body>
</html>
