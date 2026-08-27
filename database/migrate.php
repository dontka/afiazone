<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\MigrationRunner;

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

$databaseConfig = require BASE_PATH . '/config/database.php';
Database::configure($databaseConfig);

try {
    try {
        $connection = Database::connection();
    } catch (PDOException $exception) {
        $mysqlErrorCode = (int) ($exception->errorInfo[1] ?? 0);
        if ($mysqlErrorCode !== 1049) {
            throw $exception;
        }

        $serverDsn = sprintf(
            'mysql:host=%s;port=%s;charset=%s',
            $databaseConfig['host'] ?? '127.0.0.1',
            $databaseConfig['port'] ?? '3306',
            $databaseConfig['charset'] ?? 'utf8mb4'
        );
        $serverConnection = new PDO(
            $serverDsn,
            $databaseConfig['username'] ?? 'root',
            $databaseConfig['password'] ?? '',
            $databaseConfig['options'] ?? []
        );
        $databaseName = str_replace('`', '``', (string) ($databaseConfig['database'] ?? 'afyazone_dev'));
        $serverConnection->exec(
            'CREATE DATABASE IF NOT EXISTS `' . $databaseName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );

        Database::configure($databaseConfig);
        $connection = Database::connection();
    }

    $runner = new MigrationRunner(
        $connection,
        BASE_PATH . '/database/migrations'
    );
    $executed = $runner->run();

    if ($executed === []) {
        fwrite(STDOUT, "Aucune migration en attente.\n");
        exit(0);
    }

    foreach ($executed as $migration) {
        fwrite(STDOUT, "Migration executee : {$migration}\n");
    }
} catch (Throwable $exception) {
    fwrite(STDERR, "Echec des migrations : {$exception->getMessage()}\n");
    exit(1);
}