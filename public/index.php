<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Request;
use App\Core\Session;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Shared/Helpers/functions.php';

load_env(BASE_PATH . '/.env');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

$config = require BASE_PATH . '/config/app.php';

date_default_timezone_set((string) ($config['timezone'] ?? 'UTC'));

if ((bool) ($config['debug'] ?? false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

Session::start(
    BASE_PATH . '/storage/sessions',
    ($config['env'] ?? 'local') === 'production'
);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

$app = new App($config);
$app->loadRoutes($config['modules'] ?? []);
$app->run(Request::capture());