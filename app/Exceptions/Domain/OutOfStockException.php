<?php

namespace App\Exceptions\Domain;

class OutOfStockException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 409);
    }
}
