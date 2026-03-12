#!/usr/bin/env php
<?php

/**
 * AfiaZone — Migration Runner
 *
 * Manages database migrations (up/down/status/reset/fresh).
 *
 * Usage:
 *   php bin/migrate.php                 Run pending migrations
 *   php bin/migrate.php up              Run pending migrations
 *   php bin/migrate.php down [n]        Rollback last [n] migrations (default: 1)
 *   php bin/migrate.php status          Show migration status
 *   php bin/migrate.php reset           Rollback ALL migrations
 *   php bin/migrate.php fresh           Drop everything, run all migrations + seed
 *   php bin/migrate.php create <name>   Scaffold a new migration file
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/helpers.php';

// Load environment
if (file_exists(BASE_PATH . '/.env')) {
    \Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();
}

// ── Database connection ─────────────────────────────
$dbHost = env('DB_HOST', 'localhost');
$dbPort = (int) env('DB_PORT', 3306);
$dbName = env('DB_NAME', 'afiazone');
$dbUser = env('DB_USER', 'root');
$dbPassword = env('DB_PASSWORD', '');

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "❌ Database connection failed: {$e->getMessage()}\n";
    exit(1);
}

// ── Ensure migrations table exists ──────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL,
        executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
");

// ── Helpers ─────────────────────────────────────────
$migrationsDir = BASE_PATH . '/database/migrations';

function getMigrationFiles(string $dir): array
{
    $files = glob($dir . '/*.php');
    sort($files);
    return $files;
}

function getExecutedMigrations(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT migration, batch FROM migrations ORDER BY id');
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function getLastBatch(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn();
}

function migrationName(string $file): string
{
    return basename($file, '.php');
}

// ── Commands ────────────────────────────────────────
$command = $argv[1] ?? 'up';

switch ($command) {
    case 'up':
        runUp($pdo, $migrationsDir);
        break;

    case 'down':
        $steps = (int) ($argv[2] ?? 1);
        runDown($pdo, $migrationsDir, $steps);
        break;

    case 'status':
        showStatus($pdo, $migrationsDir);
        break;

    case 'reset':
        runReset($pdo, $migrationsDir);
        break;

    case 'fresh':
        runFresh($pdo, $migrationsDir, $dbName);
        break;

    case 'create':
        $name = $argv[2] ?? null;
        if (!$name) {
            echo "Usage: php bin/migrate.php create <name>\n";
            exit(1);
        }
        createMigration($migrationsDir, $name);
        break;

    default:
        echo "Unknown command: {$command}\n";
        echo "Available: up, down [n], status, reset, fresh, create <name>\n";
        exit(1);
}

// ═══════════════════════════════════════════════════
// Command implementations
// ═══════════════════════════════════════════════════

function runUp(PDO $pdo, string $dir): void
{
    echo "🔼 Running pending migrations...\n\n";

    $files = getMigrationFiles($dir);
    $executed = getExecutedMigrations($pdo);
    $batch = getLastBatch($pdo) + 1;
    $ran = 0;

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    foreach ($files as $file) {
        $name = migrationName($file);
        if (isset($executed[$name])) {
            continue;
        }

        echo "  ▸ Migrating: {$name}...";
        try {
            $migration = require $file;
            if (is_array($migration) && isset($migration['up'])) {
                ($migration['up'])($pdo);
            }

            $stmt = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
            $stmt->execute([$name, $batch]);

            echo " ✓\n";
            $ran++;
        } catch (\Throwable $e) {
            echo " ✗\n";
            echo "    Error: {$e->getMessage()}\n";
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            exit(1);
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    if ($ran === 0) {
        echo "  Nothing to migrate.\n";
    } else {
        echo "\n✅ Ran {$ran} migration(s) in batch {$batch}.\n";
    }
}

function runDown(PDO $pdo, string $dir, int $steps = 1): void
{
    echo "🔽 Rolling back {$steps} batch(es)...\n\n";

    $lastBatch = getLastBatch($pdo);
    if ($lastBatch === 0) {
        echo "  Nothing to rollback.\n";
        return;
    }

    $targetBatch = max(1, $lastBatch - $steps + 1);

    $stmt = $pdo->prepare(
        'SELECT migration FROM migrations WHERE batch >= ? ORDER BY id DESC'
    );
    $stmt->execute([$targetBatch]);
    $toRollback = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $rolled = 0;

    foreach ($toRollback as $name) {
        $file = $dir . '/' . $name . '.php';
        if (!file_exists($file)) {
            echo "  ⚠ File not found: {$name}.php — skipping\n";
            continue;
        }

        echo "  ◂ Rolling back: {$name}...";
        try {
            $migration = require $file;
            if (is_array($migration) && isset($migration['down'])) {
                ($migration['down'])($pdo);
            }

            $delStmt = $pdo->prepare('DELETE FROM migrations WHERE migration = ?');
            $delStmt->execute([$name]);

            echo " ✓\n";
            $rolled++;
        } catch (\Throwable $e) {
            echo " ✗\n";
            echo "    Error: {$e->getMessage()}\n";
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            exit(1);
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "\n✅ Rolled back {$rolled} migration(s).\n";
}

function runReset(PDO $pdo, string $dir): void
{
    echo "⏪ Resetting ALL migrations...\n\n";
    $lastBatch = getLastBatch($pdo);
    if ($lastBatch === 0) {
        echo "  Nothing to reset.\n";
        return;
    }
    runDown($pdo, $dir, $lastBatch);
}

function runFresh(PDO $pdo, string $dir, string $dbName): void
{
    echo "🔄 Fresh migration (drop all + re-migrate)...\n\n";

    // Drop all tables
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    echo "  Dropped " . count($tables) . " table(s).\n\n";

    // Re-create migrations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");

    runUp($pdo, $dir);
}

function showStatus(PDO $pdo, string $dir): void
{
    echo "📋 Migration Status\n";
    echo str_repeat('─', 60) . "\n";
    printf("  %-50s %s\n", 'Migration', 'Status');
    echo str_repeat('─', 60) . "\n";

    $files = getMigrationFiles($dir);
    $executed = getExecutedMigrations($pdo);

    foreach ($files as $file) {
        $name = migrationName($file);
        if (isset($executed[$name])) {
            printf("  %-50s \033[32m✓ Batch %d\033[0m\n", $name, $executed[$name]);
        } else {
            printf("  %-50s \033[33m○ Pending\033[0m\n", $name);
        }
    }

    echo str_repeat('─', 60) . "\n";
    $total = count($files);
    $ran = count($executed);
    $pending = $total - $ran;
    echo "  Total: {$total} | Executed: {$ran} | Pending: {$pending}\n";
}

function createMigration(string $dir, string $name): void
{
    // Determine next number
    $files = glob($dir . '/*.php');
    $maxNum = 0;
    foreach ($files as $f) {
        if (preg_match('/^(\d+)_/', basename($f), $m)) {
            $maxNum = max($maxNum, (int) $m[1]);
        }
    }
    $num = str_pad((string) ($maxNum + 1), 3, '0', STR_PAD_LEFT);
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
    $filename = "{$num}_{$slug}.php";
    $filepath = $dir . '/' . $filename;

    $template = <<<'PHP'
<?php

declare(strict_types=1);

/**
 * Migration: %NAME%
 */

return [
    'up' => function (\PDO $pdo): void {
        $pdo->exec("
            -- TODO: Write your migration SQL here
        ");
    },

    'down' => function (\PDO $pdo): void {
        $pdo->exec("
            -- TODO: Write your rollback SQL here
        ");
    },
];
PHP;

    $content = str_replace('%NAME%', $name, $template);
    file_put_contents($filepath, $content);

    echo "✅ Created migration: database/migrations/{$filename}\n";
}
