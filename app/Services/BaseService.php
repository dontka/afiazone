<?php

declare(strict_types=1);

namespace App\Services;

use Monolog\Logger;

/**
 * Base Service — all services inherit logging and validation utilities.
 */
abstract class BaseService
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = logger();
    }

    protected function log(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    protected function logError(string $message, \Throwable $exception): void
    {
        $this->logger->error($message, [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $fieldRules) {
            $rulesList = explode('|', $fieldRules);
            $value = $data[$field] ?? null;
            foreach ($rulesList as $rule) {
                $error = $this->applyRule($field, $value, $rule, $data);
                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }
        return $errors;
    }

    private function applyRule(string $field, mixed $value, string $rule, array $data): ?string
    {
        // Parse rule:param format
        $params = [];
        if (str_contains($rule, ':')) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        return match ($rule) {
            'required' => empty($value) && $value !== '0' && $value !== 0
                ? "{$field} is required" : null,
            'email' => $value && !filter_var($value, FILTER_VALIDATE_EMAIL)
                ? "{$field} must be a valid email" : null,
            'numeric' => $value && !is_numeric($value)
                ? "{$field} must be numeric" : null,
            'min' => $value && strlen((string) $value) < (int) ($params[0] ?? 0)
                ? "{$field} must be at least {$params[0]} characters" : null,
            'max' => $value && strlen((string) $value) > (int) ($params[0] ?? 255)
                ? "{$field} must not exceed {$params[0]} characters" : null,
            'in' => $value && !in_array((string) $value, $params, true)
                ? "{$field} must be one of: " . implode(', ', $params) : null,
            default => null,
        };
    }

    protected function throwIfErrors(array $errors): void
    {
        if (!empty($errors)) {
            throw new \App\Exceptions\ValidationException('Validation failed', $errors);
        }
    }
}
