<?php

namespace App\Exceptions\Domain;

class PaymentGatewayException extends DomainException
{
    public function __construct(string $message = 'Không thể tạo giao dịch thanh toán lúc này.')
    {
        parent::__construct($message, 502);
    }
}
