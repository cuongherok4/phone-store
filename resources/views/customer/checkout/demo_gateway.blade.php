<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $method === 'VNPAY' ? 'VNPAY' : 'MoMo' }} - Cổng Thanh Toán</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: {{ $method === 'VNPAY' ? 'linear-gradient(135deg, #1e3a5f 0%, #0d2137 50%, #081525 100%)' : 'linear-gradient(135deg, #6d1035 0%, #ae2070 50%, #6d1035 100%)' }};
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .gateway-wrap {
            width: 100%;
            max-width: 480px;
        }

        /* Header branding */
        .brand-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .brand-header .logo-box {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }
        .brand-header .logo-box img { height: 28px; object-fit: contain; }
        .brand-header .logo-box span { color: white; font-weight: 700; font-size: 15px; }

        /* Main card */
        .card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        }

        /* VNPAY top bar */
        .card-topbar-vnpay {
            background: #005BAA;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-topbar-vnpay img { height: 32px; }
        .card-topbar-vnpay .secured { color: rgba(255,255,255,0.8); font-size: 11px; display: flex; align-items: center; gap: 4px; }

        /* MoMo top bar */
        .card-topbar-momo {
            background: #ae2070;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-topbar-momo img { height: 32px; }
        .card-topbar-momo .secured { color: rgba(255,255,255,0.8); font-size: 11px; display: flex; align-items: center; gap: 4px; }

        .card-body { padding: 28px 28px 0; }

        /* Merchant info */
        .merchant-info {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #e8f0fe;
        }
        .merchant-icon {
            width: 48px; height: 48px;
            background: {{ $method === 'VNPAY' ? '#005BAA' : '#ae2070' }};
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 20px; flex-shrink: 0;
        }
        .merchant-name { font-weight: 700; color: #1a1a2e; font-size: 15px; }
        .merchant-desc { font-size: 12px; color: #64748b; margin-top: 2px; }

        /* Amount display */
        .amount-box {
            text-align: center;
            padding: 20px 0 24px;
            border-bottom: 1px dashed #e2e8f0;
            margin-bottom: 20px;
        }
        .amount-label { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .amount-value { font-size: 34px; font-weight: 800; color: #0f172a; }
        .amount-value span { font-size: 18px; font-weight: 600; color: #64748b; margin-left: 4px; }
        .order-ref { font-size: 12px; color: #94a3b8; margin-top: 6px; }
        .order-ref strong { color: #475569; }

        /* Payment method tabs (visual only) */
        .method-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .method-tab {
            flex: 1;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            cursor: default;
        }
        .method-tab.active {
            border-color: {{ $method === 'VNPAY' ? '#005BAA' : '#ae2070' }};
            color: {{ $method === 'VNPAY' ? '#005BAA' : '#ae2070' }};
            background: {{ $method === 'VNPAY' ? '#e8f0fe' : '#fce4ec' }};
            font-weight: 600;
        }
        .method-tab i { display: block; font-size: 18px; margin-bottom: 4px; }

        @if($method === 'VNPAY')
        /* Card input form (VNPAY style) */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            letter-spacing: 1px;
            transition: border-color 0.2s;
        }
        .form-group input:focus { outline: none; border-color: #005BAA; box-shadow: 0 0 0 3px rgba(0,91,170,0.1); }
        .form-group input::placeholder { letter-spacing: 0; color: #cbd5e1; }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @endif

        @if($method === 'MOMO')
        /* QR Code section (MoMo style) */
        .qr-section { text-align: center; padding: 10px 0 20px; }
        .qr-box {
            width: 180px; height: 180px;
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            margin: 0 auto 14px;
            display: flex; align-items: center; justify-content: center;
            background: white;
            position: relative;
            overflow: hidden;
        }
        .qr-box .qr-img { width: 150px; height: 150px; }
        .qr-label { font-size: 12px; color: #64748b; }
        .qr-label strong { color: #ae2070; }
        .divider-or {
            display: flex; align-items: center; gap: 12px;
            color: #94a3b8; font-size: 12px; margin: 16px 0;
        }
        .divider-or::before, .divider-or::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }
        @endif

        /* Action buttons */
        .btn-pay {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 10px;
        }
        .btn-confirm {
            background: {{ $method === 'VNPAY' ? '#005BAA' : '#ae2070' }};
            color: white;
            box-shadow: 0 4px 15px {{ $method === 'VNPAY' ? 'rgba(0,91,170,0.35)' : 'rgba(174,32,112,0.35)' }};
        }
        .btn-confirm:hover {
            background: {{ $method === 'VNPAY' ? '#004a8f' : '#8e1a5b' }};
            transform: translateY(-1px);
        }
        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
        }
        .btn-cancel:hover { background: #e2e8f0; }

        /* Footer */
        .card-footer {
            padding: 16px 28px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .card-footer .lock { color: #22c55e; font-size: 13px; }
        .card-footer span { font-size: 11px; color: #94a3b8; }

        /* Demo notice */
        .demo-notice {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .demo-notice i { color: #f59e0b; margin-top: 1px; flex-shrink: 0; }
        .demo-notice p { font-size: 11px; color: #78350f; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="gateway-wrap">
        {{-- Brand header --}}
        <div class="brand-header">
            <div class="logo-box">
                @if($method === 'VNPAY')
                    <img src="https://vnpay.vn/wp-content/uploads/2020/07/Logo-VNPAYQR-update.png" alt="VNPAY">
                    <span>Cổng Thanh Toán VNPAY</span>
                @else
                    <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo">
                    <span>Ví Điện Tử MoMo</span>
                @endif
            </div>
        </div>

        <div class="card">
            {{-- Top bar --}}
            @if($method === 'VNPAY')
            <div class="card-topbar-vnpay">
                <img src="https://vnpay.vn/wp-content/uploads/2020/07/Logo-VNPAYQR-update.png" alt="VNPAY">
                <div class="secured"><i class="fas fa-lock"></i> Giao dịch bảo mật SSL</div>
            </div>
            @else
            <div class="card-topbar-momo">
                <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo">
                <div class="secured"><i class="fas fa-shield-alt"></i> Bảo mật 2 lớp</div>
            </div>
            @endif

            <div class="card-body">
                {{-- Merchant info --}}
                <div class="merchant-info">
                    <div class="merchant-icon"><i class="fas fa-store"></i></div>
                    <div>
                        <div class="merchant-name">PhoneStore</div>
                        <div class="merchant-desc">Mua sắm điện thoại trực tuyến</div>
                    </div>
                </div>

                {{-- Amount --}}
                <div class="amount-box">
                    <div class="amount-label">Số tiền thanh toán</div>
                    <div class="amount-value">
                        {{ number_format($order->total_price, 0, ',', '.') }}<span>₫</span>
                    </div>
                    <div class="order-ref">Đơn hàng: <strong>#{{ $order->id }}</strong> &nbsp;|&nbsp; PhoneStore</div>
                </div>

                {{-- Demo notice --}}
                <div class="demo-notice">
                    <i class="fas fa-flask fa-sm"></i>
                    <p>Đây là môi trường <strong>Sandbox / Test</strong>. Bấm "Xác nhận" để mô phỏng thanh toán thành công, hệ thống sẽ ghi nhận đơn hàng đã thanh toán vào database.</p>
                </div>

                @if($method === 'VNPAY')
                {{-- VNPAY style: Card form --}}
                <div class="method-tabs">
                    <div class="method-tab active">
                        <i class="fas fa-credit-card"></i>Thẻ ATM / Visa
                    </div>
                    <div class="method-tab">
                        <i class="fas fa-qrcode"></i>QR Code
                    </div>
                    <div class="method-tab">
                        <i class="fas fa-mobile-alt"></i>Ví điện tử
                    </div>
                </div>

                <div class="form-group">
                    <label>Số thẻ</label>
                    <input type="text" placeholder="9704 1985 2619 1432 198" value="9704 1985 2619 1432 198" readonly>
                </div>
                <div class="form-group">
                    <label>Tên chủ thẻ</label>
                    <input type="text" placeholder="NGUYEN VAN A" value="NGUYEN VAN A" readonly>
                </div>
                <div class="row-2">
                    <div class="form-group">
                        <label>Ngày phát hành</label>
                        <input type="text" placeholder="07/15" value="07/15" readonly>
                    </div>
                    <div class="form-group">
                        <label>OTP</label>
                        <input type="text" placeholder="123456" value="123456" readonly>
                    </div>
                </div>

                @else
                {{-- MoMo style: QR + app --}}
                <div class="method-tabs">
                    <div class="method-tab active">
                        <i class="fas fa-qrcode"></i>QR Code
                    </div>
                    <div class="method-tab">
                        <i class="fas fa-mobile-alt"></i>App MoMo
                    </div>
                </div>
                <div class="qr-section">
                    <div class="qr-box">
                        {{-- Simple SVG QR placeholder --}}
                        <svg viewBox="0 0 100 100" class="qr-img" xmlns="http://www.w3.org/2000/svg">
                            <rect width="100" height="100" fill="white"/>
                            <rect x="5" y="5" width="35" height="35" rx="3" fill="#ae2070"/>
                            <rect x="10" y="10" width="25" height="25" rx="2" fill="white"/>
                            <rect x="14" y="14" width="17" height="17" rx="1" fill="#ae2070"/>
                            <rect x="60" y="5" width="35" height="35" rx="3" fill="#ae2070"/>
                            <rect x="65" y="10" width="25" height="25" rx="2" fill="white"/>
                            <rect x="69" y="14" width="17" height="17" rx="1" fill="#ae2070"/>
                            <rect x="5" y="60" width="35" height="35" rx="3" fill="#ae2070"/>
                            <rect x="10" y="65" width="25" height="25" rx="2" fill="white"/>
                            <rect x="14" y="69" width="17" height="17" rx="1" fill="#ae2070"/>
                            <rect x="45" y="5" width="6" height="6" fill="#ae2070"/>
                            <rect x="53" y="5" width="6" height="6" fill="#ae2070"/>
                            <rect x="45" y="13" width="6" height="6" fill="#ae2070"/>
                            <rect x="53" y="13" width="6" height="6" fill="#ae2070"/>
                            <rect x="45" y="45" width="6" height="6" fill="#ae2070"/>
                            <rect x="53" y="45" width="6" height="6" fill="#ae2070"/>
                            <rect x="61" y="45" width="6" height="6" fill="#ae2070"/>
                            <rect x="69" y="45" width="6" height="6" fill="#ae2070"/>
                            <rect x="77" y="45" width="6" height="6" fill="#ae2070"/>
                            <rect x="85" y="45" width="6" height="6" fill="#ae2070"/>
                            <rect x="45" y="53" width="6" height="6" fill="#ae2070"/>
                            <rect x="61" y="53" width="6" height="6" fill="#ae2070"/>
                            <rect x="77" y="53" width="6" height="6" fill="#ae2070"/>
                            <rect x="45" y="61" width="6" height="6" fill="#ae2070"/>
                            <rect x="53" y="61" width="6" height="6" fill="#ae2070"/>
                            <rect x="69" y="61" width="6" height="6" fill="#ae2070"/>
                            <rect x="85" y="61" width="6" height="6" fill="#ae2070"/>
                            <rect x="45" y="69" width="6" height="6" fill="#ae2070"/>
                            <rect x="61" y="69" width="6" height="6" fill="#ae2070"/>
                            <rect x="77" y="69" width="6" height="6" fill="#ae2070"/>
                            <rect x="45" y="77" width="6" height="6" fill="#ae2070"/>
                            <rect x="53" y="77" width="6" height="6" fill="#ae2070"/>
                            <rect x="69" y="77" width="6" height="6" fill="#ae2070"/>
                            <rect x="45" y="85" width="6" height="6" fill="#ae2070"/>
                            <rect x="61" y="85" width="6" height="6" fill="#ae2070"/>
                            <rect x="85" y="85" width="6" height="6" fill="#ae2070"/>
                        </svg>
                    </div>
                    <div class="qr-label">Quét mã bằng <strong>App MoMo</strong> để thanh toán</div>
                </div>
                @endif

                {{-- Action buttons --}}
                <a href="{{ route('checkout.demo_callback', ['order_id' => $order->id, 'method' => $method, 'result' => 'success']) }}"
                   class="btn-pay btn-confirm">
                    <i class="fas fa-check-circle mr-1"></i>
                    {{ $method === 'VNPAY' ? 'Thanh toán ngay' : 'Xác nhận thanh toán' }}
                </a>
                <a href="{{ route('checkout.demo_callback', ['order_id' => $order->id, 'method' => $method, 'result' => 'cancel']) }}"
                   class="btn-pay btn-cancel">
                    <i class="fas fa-arrow-left mr-1"></i> Hủy, quay lại
                </a>
            </div>

            {{-- Footer --}}
            <div class="card-footer">
                <span class="lock"><i class="fas fa-lock"></i></span>
                <span>Giao dịch được bảo mật bởi SSL 256-bit</span>
                <span>|</span>
                <span>PCI DSS Compliant</span>
            </div>
        </div>
    </div>
</body>
</html>
