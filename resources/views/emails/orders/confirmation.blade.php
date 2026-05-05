<x-mail::message>
# Cảm ơn bạn đã đặt hàng!

Chào {{ $order->user->name }},

Đơn hàng của bạn đã được tiếp nhận thành công. Dưới đây là thông tin chi tiết đơn hàng:

**Mã đơn hàng:** #{{ $order->id }}  
**Ngày đặt:** {{ $order->created_at->format('d/m/Y H:i') }}  
**Tổng thanh toán:** {{ number_format($order->total_price, 0, ',', '.') }}đ

<x-mail::table>
| Sản phẩm | SL | Thành tiền |
| :--- | :---: | :--- |
@foreach($order->items as $item)
| {{ $item->name }} | {{ $item->quantity }} | {{ number_format($item->subtotal, 0, ',', '.') }}đ |
@endforeach
</x-mail::table>

**Địa chỉ giao hàng:**  
{{ $order->shipping_name }}  
{{ $order->shipping_phone }}  
{{ $order->shipping_address }}

<x-mail::button :url="route('orders.show', $order->id)">
Xem chi tiết đơn hàng
</x-mail::button>

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
