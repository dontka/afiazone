#!/usr/bin/env php
<?php

/**
 * Database Setup Script
 *
 * Creates the database and tables for AfiaZone
 */

declare(strict_types=1);

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Load autoloader and helpers
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/helpers.php';

// Load environment
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

echo "🏥 AfiaZone Database Setup\n";
echo "==========================\n\n";

// Database connection info
$dbHost = env('DB_HOST', 'localhost');
$dbPort = env('DB_PORT', 3306);
$dbName = env('DB_NAME', 'afiazone');
$dbUser = env('DB_USER', 'root');
$dbPassword = env('DB_PASSWORD', '');

echo "Connecting to MySQL at {$dbHost}:{$dbPort}...\n";

try {
    // Connect to MySQL
    $dsn = "mysql:host={$dbHost};port={$dbPort}";
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Create database
    echo "Creating database '{$dbName}'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Connect to the new database
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Load and execute schema
    $schemaFile = BASE_PATH . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: {$schemaFile}");
    }

    echo "Loading schema from database/schema.sql...\n";
    $schema = file_get_contents($schemaFile);

    // Disable FK checks during schema import (tables may reference each other)
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    // Split queries and execute
    $queries = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    echo "\n✅ Database setup completed successfully!\n";
    echo "Database: {$dbName}\n";
    echo "Host: {$dbHost}:{$dbPort}\n";
    echo "User: {$dbUser}\n";

} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
