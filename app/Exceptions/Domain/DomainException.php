<?php

namespace App\Exceptions\Domain;

use DomainException as BaseDomainException;

abstract class DomainException extends BaseDomainException
{
    public function __construct(string $message, private readonly int $statusCode = 422)
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
