<?php

declare(strict_types=1);

namespace App\Console;

class MigrateCommand extends Command
{
    public string $name = 'migrate';
    public string $description = 'Run database migrations';

    public function handle(array $arguments = []): int
    {
        $this->info('Running migrations...');

        $pdo = db();

        // Ensure migrations table exists
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL DEFAULT 1,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');

        // Get already-run migrations
        $applied = [];
        $stmt = $pdo->query('SELECT migration FROM migrations ORDER BY id');
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $applied[] = $row['migration'];
        }

        // Current batch number
        $batchStmt = $pdo->query('SELECT COALESCE(MAX(batch), 0) + 1 AS next_batch FROM migrations');
        $batch = (int) $batchStmt->fetch(\PDO::FETCH_ASSOC)['next_batch'];

        // Collect migration files
        $migrationsPath = BASE_PATH . '/database/migrations';
        if (!is_dir($migrationsPath)) {
            $this->warn('No migrations directory found.');
            return 0;
        }

        $files = glob($migrationsPath . '/*.{sql,php}', GLOB_BRACE);
        sort($files);

        $ran = 0;
        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $applied, true)) {
                continue;
            }

            $this->info("Migrating: {$name}");

            $ext = pathinfo($file, PATHINFO_EXTENSION);

            try {
                if ($ext === 'sql') {
                    $sql = file_get_contents($file);
                    $pdo->exec($sql);
                } elseif ($ext === 'php') {
                    $migration = require $file;
                    if (is_callable($migration)) {
                        $migration($pdo);
                    } elseif (is_array($migration) && isset($migration['up'])) {
                        ($migration['up'])($pdo);
                    }
                }

                $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)')
                    ->execute([$name, $batch]);

                $this->success("Migrated:  {$name}");
                $ran++;
            } catch (\Throwable $e) {
                $this->error("Migration failed: {$name} — " . $e->getMessage());
                return 1;
            }
        }

        if ($ran === 0) {
            $this->info('Nothing to migrate.');
        } else {
            $this->success("{$ran} migration(s) applied (batch {$batch}).");
        }

        return 0;
    }
}
