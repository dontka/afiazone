<?php

declare(strict_types=1);

namespace App\Console;

abstract class Command
{
    public string $name = '';
    public string $description = '';

    abstract public function handle(array $arguments = []): int;

    protected function error(string $message): void
    {
        echo "\033[31m[ERROR] {$message}\033[0m\n";
    }

    protected function success(string $message): void
    {
        echo "\033[32m[SUCCESS] {$message}\033[0m\n";
    }

    protected function info(string $message): void
    {
        echo "\033[36m[INFO] {$message}\033[0m\n";
    }

    protected function warn(string $message): void
    {
        echo "\033[33m[WARNING] {$message}\033[0m\n";
    }
}
