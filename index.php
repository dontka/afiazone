<?php

declare(strict_types=1);

/**
 * AfiaZone — Application Entry Point
 */

define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/vendor/autoload.php';

// ── Environment ───────────────────────────────
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

// ── Helpers & Config ──────────────────────────
require_once BASE_PATH . '/app/helpers.php';

$config = [
    'app' => require BASE_PATH . '/config/app.php',
    'database' => require BASE_PATH . '/config/database.php',
    'services' => require BASE_PATH . '/config/services.php',
    'cache' => require BASE_PATH . '/config/cache.php',
];

// ── Error handling ────────────────────────────
if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

date_default_timezone_set(config('app.timezone'));

// ── Global middleware (CORS, Rate-Limit, Logging) ──
$globalMiddleware = [
    new \App\Middleware\CorsMiddleware(),
    new \App\Middleware\RateLimitMiddleware(),
    new \App\Middleware\LoggingMiddleware(),
];

foreach ($globalMiddleware as $mw) {
    $mw->handle();
}

// ── Routing ───────────────────────────────────
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestPath = rtrim(str_replace('/index.php', '', $requestPath), '/') ?: '/';

// Strip base path when running in a subdirectory (e.g. /afiazone)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($scriptDir !== '' && str_starts_with($requestPath, $scriptDir)) {
    $requestPath = substr($requestPath, strlen($scriptDir)) ?: '/';
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

// Serve static files from public/
if ($requestPath !== '/' && is_file(BASE_PATH . '/public' . $requestPath)) {
    return false;
}

$routes = require BASE_PATH . '/routes/api.php';
$matchedRoute = null;
$routeParams = [];

foreach ($routes as $route) {
    if ($route['method'] !== $requestMethod) {
        continue;
    }

    $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
    $pattern = '@^' . $pattern . '$@';

    if (preg_match($pattern, $requestPath, $matches)) {
        $matchedRoute = $route;
        $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        break;
    }
}

if ($matchedRoute === null) {
    abort(404, 'Endpoint not found');
}

// ── Route middleware ──────────────────────────
$middlewareNames = $matchedRoute['middleware'] ?? [];
foreach ($middlewareNames as $entry) {
    // Support format "name:param1,param2" e.g. "rbac:admin,moderator"
    $parts = explode(':', $entry, 2);
    $name = $parts[0];
    $params = isset($parts[1]) ? explode(',', $parts[1]) : [];

    $class = 'App\\Middleware\\' . ucfirst($name) . 'Middleware';
    if (class_exists($class)) {
        $mw = !empty($params) ? new $class($params) : new $class();
        $mw->handle();
    }
}

// ── Dispatch controller ──────────────────────
[$controllerName, $action] = explode('@', $matchedRoute['controller']);
$controllerClass = 'App\\Controllers\\' . $controllerName;

if (!class_exists($controllerClass)) {
    abort(500, "Controller not found: {$controllerClass}");
}

$controller = new $controllerClass();

if (!method_exists($controller, $action)) {
    abort(500, "Method not found: {$action}");
}

try {
    // Pass route params as typed arguments
    $args = [];
    $ref = new \ReflectionMethod($controller, $action);
    foreach ($ref->getParameters() as $param) {
        $name = $param->getName();
        if (isset($routeParams[$name])) {
            $type = $param->getType();
            $args[] = ($type && $type->getName() === 'int')
                ? (int) $routeParams[$name]
                : $routeParams[$name];
        }
    }

    call_user_func_array([$controller, $action], $args);
} catch (\App\Exceptions\ValidationException $e) {
    response([
        'success' => false,
        'message' => $e->getMessage(),
        'errors' => $e->getErrors(),
    ], 422);
} catch (\App\Exceptions\HttpException $e) {
    abort($e->getStatusCode(), $e->getMessage());
} catch (\Throwable $e) {
    logger()->error('Unhandled exception', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    $msg = config('app.debug') ? $e->getMessage() : 'Internal Server Error';
    abort(500, $msg);
}
