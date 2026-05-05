<x-mail::message>
# Cập nhật trạng thái đơn hàng

Chào {{ $order->user->name }},

Đơn hàng **#{{ $order->id }}** của bạn đã được chuyển sang trạng thái: **{{ $newStatus }}**.

@if($newStatus === 'SHIPPING')
Đơn hàng đang trên đường giao đến bạn. Vui lòng giữ điện thoại để shipper có thể liên lạc.
@elseif($newStatus === 'COMPLETED')
Cảm ơn bạn đã mua sắm tại PhoneStore! Hy vọng bạn hài lòng với sản phẩm.
@elseif($newStatus === 'CANCELLED')
Rất tiếc, đơn hàng của bạn đã bị huỷ. Nếu có thắc mắc, vui lòng liên hệ hotline của chúng tôi.
@endif

<x-mail::button :url="route('orders.show', $order->id)">
Xem chi tiết đơn hàng
</x-mail::button>

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
