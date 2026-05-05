<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hoá đơn #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }
        .company-info h1 {
            color: #3b82f6;
            margin: 0;
            font-size: 28px;
        }
        .invoice-info {
            text-align: right;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            font-size: 12px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f9fafb;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #eee;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .totals {
            margin-top: 30px;
            text-align: right;
        }
        .totals table {
            width: 300px;
            float: right;
        }
        .totals table tr td:first-child {
            text-align: left;
            font-weight: bold;
        }
        .grand-total {
            color: #ef4444;
            font-size: 20px;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="company-info">
                <h1>PHONESTORE</h1>
                <p>Số 123, Đường ABC, TP. Hồ Chí Minh<br>
                Hotline: 1900 1234 | Email: contact@phonestore.vn</p>
            </div>
            <div class="invoice-info">
                <h2>HOÁ ĐƠN BÁN HÀNG</h2>
                <p>Số hoá đơn: <strong>#{{ $order->id }}</strong><br>
                Ngày lập: {{ now()->format('d/m/Y') }}<br>
                Trạng thái: {{ \App\Models\Order::getLabel($order->status) }}</p>
            </div>
        </div>

        <div style="clear: both;"></div>

        <div class="section">
            <div class="section-title">Thông tin khách hàng</div>
            <p><strong>Người nhận:</strong> {{ $order->shipping_name }}<br>
            <strong>Điện thoại:</strong> {{ $order->shipping_phone }}<br>
            <strong>Địa chỉ:</strong> {{ $order->shipping_address }}<br>
            <strong>Ghi chú:</strong> {{ $order->note ?? 'Không có' }}</p>
        </div>

        <div class="section">
            <div class="section-title">Danh sách sản phẩm</div>
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>SKU</th>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: right;">Đơn giá</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->sku }}</td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">{{ number_format($item->price, 0, ',', '.') }}đ</td>
                            <td style="text-align: right;">{{ number_format($item->subtotal, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals">
            <table>
                <tr>
                    <td>Tạm tính:</td>
                    <td>{{ number_format($order->subtotal, 0, ',', '.') }}đ</td>
                </tr>
                <tr>
                    <td>Phí vận chuyển:</td>
                    <td>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</td>
                </tr>
                @if($order->discount_amount > 0)
                    <tr>
                        <td>Giảm giá:</td>
                        <td>-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td>Tổng thanh toán:</td>
                    <td>{{ number_format($order->total_price, 0, ',', '.') }}đ</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <div class="footer">
            <p>Cảm ơn quý khách đã tin tưởng và mua sắm tại PhoneStore!<br>
            Quý khách vui lòng kiểm tra kỹ hàng hoá trước khi thanh toán.</p>
        </div>
    </div>
</body>
</html>
