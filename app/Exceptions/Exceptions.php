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

/**
 * 400 – Validation errors
 */
class ValidationException extends HttpException
{
    private array $errors;

    public function __construct(string $message = 'Validation failed', array $errors = [], ?Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, 422, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

/**
 * 401 – Authentication failure
 */
class UnauthorizedException extends HttpException
{
    public function __construct(string $message = 'Unauthorized', ?Exception $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}

/**
 * 403 – Insufficient permissions
 */
class ForbiddenException extends HttpException
{
    public function __construct(string $message = 'Forbidden', ?Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}

/**
 * 404 – Resource not found
 */
class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Not found', ?Exception $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
