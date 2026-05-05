<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Tạo link thanh toán MoMo (Sandbox / Production)
     */
    public function createMomoPayment(Order $order)
    {
        $partnerCode = config('payment.momo.partner_code');
        $endpoint    = config('payment.momo.endpoint');
        $accessKey   = config('payment.momo.access_key');
        $secretKey   = config('payment.momo.secret_key');
        $returnUrl   = url(config('payment.momo.return_url'));

        // Sandbox chấp nhận localhost cho cả redirect và IPN
        $ipnUrl = $returnUrl;

        $orderInfo   = 'Thanh toan don hang ' . $order->id;
        $amount      = (string)(int)$order->total_price;
        $timestamp   = time();
        $orderId     = $order->id . '_' . $timestamp;
        $requestId   = (string)$timestamp;   // requestId = time() riêng
        $requestType = 'captureWallet';
        $extraData   = '';  // để trống, MoMo log xác nhận đây là chuỗi rỗng

        $rawHash = 'accessKey='  . $accessKey
            . '&amount='      . $amount
            . '&extraData='   . $extraData
            . '&ipnUrl='      . $ipnUrl
            . '&orderId='     . $orderId
            . '&orderInfo='   . $orderInfo
            . '&partnerCode=' . $partnerCode
            . '&redirectUrl=' . $returnUrl
            . '&requestId='   . $requestId
            . '&requestType=' . $requestType;

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'PhoneStore',
            'storeId'     => 'PhoneStore',
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature,
        ];

        Log::info('[MoMo] Sending request', ['orderId' => $orderId, 'amount' => $amount]);

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint, $data);

        $result = $response->json();

        Log::info('[MoMo] Response', $result ?? []);

        if (isset($result['payUrl']) && !empty($result['payUrl'])) {
            return $result['payUrl'];
        }

        $errorMsg = $result['message'] ?? $result['localMessage'] ?? 'Unknown Error';
        Log::error('[MoMo] Failed: ' . json_encode($result));
        throw new \Exception('MoMo: ' . $errorMsg . ' (resultCode: ' . ($result['resultCode'] ?? 'N/A') . ')');
    }

    /**
     * Kiểm tra VNPAY credentials, fallback sang demo gateway nếu chưa có
     */
    protected function isVnpayConfigured(): bool
    {
        $code = \App\Models\Setting::get('vnpay_tmn_code');
        return !empty($code) && strlen(trim($code)) >= 4; 
    }

    /**
     * Tạo link thanh toán VNPAY
     */
    public function createVnpayPayment(Order $order)
    {
        // Nếu chưa cấu hình credentials thật, dùng trang demo nội bộ
        if (!$this->isVnpayConfigured()) {
            return route('checkout.demo_gateway', [
                'order_id'  => $order->id,
                'method'    => 'VNPAY',
                'amount'    => $order->total_price,
            ]);
        }

        $vnp_TmnCode   = \App\Models\Setting::get('vnpay_tmn_code');
        $vnp_HashSecret = \App\Models\Setting::get('vnpay_hash_secret');
        $vnp_Url       = \App\Models\Setting::get('vnpay_url');
        $vnp_Returnurl = url(config('payment.vnpay.return_url'));

        $vnp_TxnRef   = $order->id . '_' . time();
        $vnp_OrderInfo = "Thanh toan don hang {$order->id}";
        $vnp_Amount   = $order->total_price * 100;
        $vnp_IpAddr   = request()->ip() ?? '127.0.0.1';

        $inputData = [
            "vnp_Version"   => "2.1.0",
            "vnp_TmnCode"   => $vnp_TmnCode,
            "vnp_Amount"    => $vnp_Amount,
            "vnp_Command"   => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr"   => $vnp_IpAddr,
            "vnp_Locale"   => 'vn',
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => 'billpayment',
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef"   => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = $hashdata = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        $finalUrl = $vnp_Url . '?' . $query;
        if ($vnp_HashSecret) {
            $finalUrl .= 'vnp_SecureHash=' . hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        }

        return $finalUrl;
    }

    /**
     * Xử lý kết quả trả về từ VNPAY.
     */
    public function verifyVnpayPayment($inputData)
    {
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']); // Xóa type nếu có

        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $vnp_HashSecret = \App\Models\Setting::get('vnpay_hash_secret');
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00') {
                // Thành công
                $txnRefParts = explode('_', $inputData['vnp_TxnRef']);
                $orderId = $txnRefParts[0];
                $order = Order::find($orderId);
                
                if ($order && $order->payment_status !== 'PAID') {
                    // Lưu record
                    $this->savePaymentRecord($order, 'VNPAY', $inputData['vnp_TransactionNo'], $order->total_price, 'SUCCESS', json_encode($inputData));
                    
                    return ['success' => true, 'order_id' => $order->id];
                }
            }
        }
        
        return ['success' => false, 'message' => 'Chữ ký không hợp lệ hoặc thanh toán thất bại'];
    }

    /**
     * Lưu thông tin giao dịch vào db
     */
    protected function savePaymentRecord($order, $method, $transactionId, $amount, $status, $response)
    {
        \App\Models\Payment::create([
            'order_id' => $order->id,
            'method' => $method,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => $status,
            'gateway_response' => $response,
            'paid_at' => now(),
        ]);
    }

    /**
     * Xử lý kết quả trả về từ MoMo (redirect callback).
     */
    public function verifyMomoPayment($inputData)
    {
        Log::info('[MoMo] Callback received', $inputData);

        $partnerCode  = $inputData['partnerCode']  ?? '';
        $orderId      = $inputData['orderId']       ?? '';
        $requestId    = $inputData['requestId']     ?? '';
        $amount       = $inputData['amount']        ?? '';
        $orderInfo    = $inputData['orderInfo']     ?? '';
        $orderType    = $inputData['orderType']     ?? '';
        $transId      = $inputData['transId']       ?? '';
        $resultCode   = $inputData['resultCode']    ?? '-1';
        $message      = $inputData['message']       ?? '';
        $payType      = $inputData['payType']       ?? '';
        $responseTime = $inputData['responseTime']  ?? '';
        $extraData    = $inputData['extraData']     ?? '';
        $m2signature  = $inputData['signature']     ?? '';

        $accessKey = config('payment.momo.access_key');
        $secretKey = config('payment.momo.secret_key');

        // Reconstruct raw hash theo chuẩn MoMo v2
        $rawHash = 'accessKey='  . $accessKey
            . '&amount='      . $amount
            . '&extraData='   . $extraData
            . '&message='     . $message
            . '&orderId='     . $orderId
            . '&orderInfo='   . $orderInfo
            . '&orderType='   . $orderType
            . '&partnerCode=' . $partnerCode
            . '&payType='     . $payType
            . '&requestId='   . $requestId
            . '&responseTime='. $responseTime
            . '&resultCode='  . $resultCode
            . '&transId='     . $transId;

        $partnerSignature = hash_hmac('sha256', $rawHash, $secretKey);

        Log::info('[MoMo] resultCode=' . $resultCode . ' signatureMatch=' . ($m2signature === $partnerSignature ? 'YES' : 'NO'));

        // Lấy orderId thật: thử từ extraData trước, fallback về split '_'
        $realOrderId = null;
        if (!empty($extraData)) {
            $decoded = json_decode(base64_decode($extraData), true);
            $realOrderId = $decoded['orderId'] ?? null;
        }
        if (!$realOrderId) {
            $parts = explode('_', $orderId);
            $realOrderId = $parts[0];
        }

        // Chấp nhận khi: chữ ký đúng VÀ resultCode = 0
        if ($m2signature === $partnerSignature && $resultCode == '0') {
            $order = Order::find($realOrderId);

            if ($order && $order->payment_status !== 'PAID') {
                $this->savePaymentRecord($order, 'MOMO', (string)$transId, $amount, 'SUCCESS', json_encode($inputData));
                Log::info('[MoMo] Payment SUCCESS for order ' . $order->id);
                return ['success' => true, 'order_id' => $order->id];
            }

            // Đơn đã được xử lý trước đó (idempotent)
            if ($order && $order->payment_status === 'PAID') {
                return ['success' => true, 'order_id' => $order->id];
            }
        }

        $reason = $resultCode != '0'
            ? 'Thanh toán thất bại (resultCode=' . $resultCode . '): ' . $message
            : 'Chữ ký không hợp lệ';

        Log::warning('[MoMo] Payment FAILED: ' . $reason);
        return ['success' => false, 'message' => $reason];
    }
}
