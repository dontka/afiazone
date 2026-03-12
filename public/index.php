<?php

declare(strict_types=1);

/**
 * Application Entry Point
 *
 * AfiaZone Medical Marketplace
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Load environment variables
require_once BASE_PATH . '/vendor/autoload.php';

// Load .env file
if (file_exists(BASE_PATH . '/.env')) {
    $env = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Load helper functions
require_once BASE_PATH . '/app/helpers.php';

// Load configuration
$config = [
    'app' => require BASE_PATH . '/config/app.php',
    'database' => require BASE_PATH . '/config/database.php',
];

// Set error handling based on debug mode
if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

// Set timezone
date_default_timezone_set(config('app.timezone'));

// CORS Headers
header('Access-Control-Allow-Origin: ' . env('APP_URL'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// Simple Router
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestPath = str_replace('/index.php', '', $requestPath);
$requestFile = BASE_PATH . '/public' . $requestPath;

// Serve static files
if ($requestPath !== '/' && file_exists($requestFile) && is_file($requestFile)) {
    return false;
}

// Health check endpoint
if ($requestPath === '/health') {
    response(['status' => 'ok', 'timestamp' => date('c')], 200);
    exit;
}

// API Routes
$routes = require BASE_PATH . '/routes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$matchedRoute = null;

foreach ($routes as $route) {
    if ($route['method'] !== $method) {
        continue;
    }

    // Convert route path to regex (e.g., /users/{id} -> /users/(\d+))
    $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
    $pattern = '@^' . $pattern . '$@';

    if (preg_match($pattern, $requestPath, $matches)) {
        $matchedRoute = $route;
        $_GET += array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        break;
    }
}

if ($matchedRoute === null) {
    abort(404, 'Endpoint not found');
}

// Parse controller and method
[$controllerName, $methodName] = explode('@', $matchedRoute['controller']);
$controllerClass = 'App\\Controllers\\' . $controllerName;

// Apply middleware
$middleware = $matchedRoute['middleware'] ?? [];
foreach ($middleware as $middlewareEntry) {
    $params = [];
    $middlewareName = $middlewareEntry;

    // Parse parameters: "rbac:admin,moderator" => name="rbac", params=["admin","moderator"]
    if (str_contains($middlewareEntry, ':')) {
        [$middlewareName, $paramStr] = explode(':', $middlewareEntry, 2);
        $params = explode(',', $paramStr);
    }

    $middlewareClass = 'App\\Middleware\\' . ucfirst($middlewareName) . 'Middleware';
    if (class_exists($middlewareClass)) {
        $middlewareInstance = empty($params)
            ? new $middlewareClass()
            : new $middlewareClass($params);
        if (!$middlewareInstance->handle()) {
            abort(401, 'Unauthorized');
        }
    }
}

// Execute controller
if (!class_exists($controllerClass)) {
    abort(500, 'Controller not found: ' . $controllerClass);
}

$controller = new $controllerClass();

if (!method_exists($controller, $methodName)) {
    abort(500, 'Method not found: ' . $methodName);
}

$routeParams = array_values(array_filter($matches ?? [], 'is_string', ARRAY_FILTER_USE_KEY));
$reflectionMethod = new ReflectionMethod($controller, $methodName);
$invokeArgs = [];

foreach ($reflectionMethod->getParameters() as $index => $parameter) {
    $value = $routeParams[$index] ?? null;
    $type = $parameter->getType();

    if ($value !== null && $type instanceof ReflectionNamedType && $type->isBuiltin()) {
        $value = match ($type->getName()) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'string' => (string) $value,
            default => $value,
        };
    }

    if ($value === null && !$parameter->isOptional()) {
        abort(500, 'Missing route parameter: ' . $parameter->getName());
    }

    $invokeArgs[] = $value ?? $parameter->getDefaultValue();
}

// Execute
try {
    $reflectionMethod->invokeArgs($controller, $invokeArgs);
} catch (\App\Exceptions\HttpException $e) {
    http_response_code($e->getStatusCode());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (\Throwable $e) {
    logger('Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    abort(500, 'Internal Server Error', ['error' => $e->getMessage()]);
}
