<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class Logger
{
    public function __construct(private readonly string $logPath)
    {
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function exception(Throwable $exception, array $context = []): void
    {
        $this->error($exception->getMessage(), array_merge($context, [
            'exception' => $exception::class,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]));
    }

    private function write(string $level, string $message, array $context): void
    {
        $directory = dirname($this->logPath);
        if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
            return;
        }

        $record = [
            'timestamp' => date(DATE_ATOM),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
        error_log((json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}') . PHP_EOL, 3, $this->logPath);
    }
}