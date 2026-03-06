<?php

declare(strict_types=1);

/**
 * Helper Functions for AfiaZone Application
 */

if (! function_exists('env')) {
    /**
     * Get environment variable
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        return match (strtolower($value)) {
            'null' => null,
            'true' => true,
            'false' => false,
            default => $value,
        };
    }
}

if (! function_exists('base_path')) {
    /**
     * Get base path
     */
    function base_path(string $path = ''): string
    {
        return __DIR__ . '/..' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('app_path')) {
    /**
     * Get app path
     */
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (! function_exists('config_path')) {
    /**
     * Get config path
     */
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (! function_exists('database_path')) {
    /**
     * Get database path
     */
    function database_path(string $path = ''): string
    {
        return base_path('database' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (! function_exists('public_path')) {
    /**
     * Get public path
     */
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get storage path
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (! function_exists('resources_path')) {
    /**
     * Get resources path
     */
    function resources_path(string $path = ''): string
    {
        return base_path('resources' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (! function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $config = [];

        if (empty($config)) {
            // Load all config files
            $configDir = config_path();
            if (is_dir($configDir)) {
                foreach (glob($configDir . '/*.php') as $file) {
                    $configKey = basename($file, '.php');
                    $config[$configKey] = require $file;
                }
            }
        }

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (! isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

if (! function_exists('response')) {
    /**
     * Create JSON response
     */
    function response(
        array $data = [],
        int $statusCode = 200,
        array $headers = []
    ): void {
        header('Content-Type: application/json', true, $statusCode);

        foreach ($headers as $header => $value) {
            header("{$header}: {$value}");
        }

        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

if (! function_exists('abort')) {
    /**
     * Abort with error response
     */
    function abort(
        int $statusCode = 500,
        string $message = 'Internal Server Error',
        array $data = []
    ): void {
        response([
            'success' => false,
            'message' => $message,
            'errors' => $data,
        ], $statusCode);
        exit;
    }
}

if (! function_exists('success')) {
    /**
     * Success response
     */
    function success(
        array $data = [],
        string $message = 'Success',
        int $statusCode = 200
    ): void {
        response([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}

if (! function_exists('error')) {
    /**
     * Error response
     */
    function error(
        string $message = 'Error',
        array $errors = [],
        int $statusCode = 400
    ): void {
        response([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}

if (! function_exists('json_response')) {
    /**
     * JSON response
     */
    function json_response(
        bool $success,
        string $message = '',
        mixed $data = null,
        int $statusCode = 200
    ): void {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        response($response, $statusCode);
    }
}

if (! function_exists('logger')) {
    /**
     * Get Monolog logger instance, or log a message directly
     */
    function logger(?string $message = null, array $context = []): \Monolog\Logger
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new \Monolog\Logger('afiazone');
            $logPath = storage_path('logs/app.log');
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0775, true);
            }
            $instance->pushHandler(
                new \Monolog\Handler\RotatingFileHandler($logPath, 30, \Monolog\Level::Debug)
            );
        }

        if ($message !== null) {
            $instance->info($message, $context);
        }

        return $instance;
    }
}

if (! function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(mixed ...$values): void
    {
        foreach ($values as $value) {
            var_dump($value);
        }
        exit;
    }
}

if (! function_exists('isset_required')) {
    /**
     * Check if required parameter is set
     */
    function isset_required(array $data, array $required): bool
    {
        foreach ($required as $key) {
            if (! isset($data[$key]) || empty($data[$key])) {
                return false;
            }
        }

        return true;
    }
}

if (! function_exists('clean_input')) {
    /**
     * Clean input data
     */
    function clean_input(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map('clean_input', $value);
        }

        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }
}

if (! function_exists('validate_email')) {
    /**
     * Validate email address
     */
    function validate_email(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (! function_exists('validate_phone')) {
    /**
     * Validate phone number
     */
    function validate_phone(string $phone): bool
    {
        // Simple phone validation - adjust as needed
        return (bool) preg_match('/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/', $phone);
    }
}

if (! function_exists('generate_token')) {
    /**
     * Generate secure token
     */
    function generate_token(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}

if (! function_exists('generate_code')) {
    /**
     * Generate numeric code
     */
    function generate_code(int $length = 6): string
    {
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('hash_password')) {
    /**
     * Hash password
     */
    function hash_password(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}

if (! function_exists('verify_password')) {
    /**
     * Verify password
     */
    function verify_password(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

if (! function_exists('format_currency')) {
    /**
     * Format currency value
     */
    function format_currency(float $amount, string $currency = 'CDF', int $decimals = 2): string
    {
        return number_format($amount, $decimals, '.', ',') . ' ' . $currency;
    }
}

if (! function_exists('format_date')) {
    /**
     * Format date
     */
    function format_date(string $date, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $dateTime = new DateTime($date);

            return $dateTime->format($format);
        } catch (Exception) {
            return $date;
        }
    }
}

if (! function_exists('is_json')) {
    /**
     * Check if string is valid JSON
     */
    function is_json(string $string): bool
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (! function_exists('array_get')) {
    /**
     * Get array value by dot notation
     */
    function array_get(array $array, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (! isset($array[$k])) {
                return $default;
            }
            $array = $array[$k];
        }

        return $array;
    }
}

if (! function_exists('db')) {
    /**
     * Get database connection
     */
    function db(): ?PDO
    {
        static $connection = null;

        if ($connection === null) {
            try {
                $dbConfig = config('database.connections.' . config('database.default'));
                
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $dbConfig['host'],
                    $dbConfig['port'],
                    $dbConfig['database'],
                    $dbConfig['charset']
                );

                $connection = new PDO(
                    $dsn,
                    $dbConfig['username'],
                    $dbConfig['password'],
                    $dbConfig['options'] ?? []
                );
            } catch (PDOException $e) {
                logger('Database connection error', ['error' => $e->getMessage()]);
                return null;
            }
        }

        return $connection;
    }
}

if (! function_exists('view_path')) {
    /**
     * Get view file path
     */
    function view_path(string $view = ''): string
    {
        $file = str_replace('.', '/', $view) . '.php';
        return base_path('html/' . $file);
    }
}

if (! function_exists('cache')) {
    /**
     * Get/set cache value
     */
    function cache(string $key, mixed $value = null, int $ttl = 3600): mixed
    {
        // File-based cache implementation
        static $store = [];

        if ($value === null) {
            return $store[$key] ?? null;
        }

        $store[$key] = $value;
        return $value;
    }
}

if (! function_exists('redirect')) {
    /**
     * Redirect to URL
     */
    function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit;
    }
}

if (! function_exists('route')) {
    /**
     * Generate URL for route
     */
    function route(string $name, array $params = []): string
    {
        // TODO: Implement route name resolution
        return '/';
    }
}
