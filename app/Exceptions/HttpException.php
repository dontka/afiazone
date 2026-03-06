<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Base HTTP Exception
 */
class HttpException extends Exception
{
    protected int $statusCode = 500;

    public function __construct(
        string $message = '',
        int $statusCode = 500,
        ?Exception $previous = null
    ) {
        $this->statusCode = $statusCode;
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
