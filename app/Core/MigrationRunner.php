<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

class MigrationRunner
{
    public function __construct(
        private readonly PDO $connection,
        private readonly string $migrationsPath
    ) {
    }

    public function run(): array
    {
        $this->ensureMigrationsTable();

        $applied = $this->connection
            ->query('SELECT migration FROM migrations ORDER BY batch, id')
            ->fetchAll(PDO::FETCH_COLUMN);
        $appliedLookup = array_fill_keys($applied, true);

        $files = glob($this->migrationsPath . '/*.php') ?: [];
        sort($files, SORT_STRING);

        $batch = (int) $this->connection
            ->query('SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations')
            ->fetchColumn();
        $executed = [];

        foreach ($files as $file) {
            $migration = basename($file, '.php');

            if (isset($appliedLookup[$migration])) {
                continue;
            }

            $callback = require $file;
            if (! is_callable($callback)) {
                throw new RuntimeException('Migration must return a callable: ' . $file);
            }

            $this->connection->beginTransaction();

            try {
                $callback($this->connection);
                $statement = $this->connection->prepare(
                    'INSERT INTO migrations (migration, batch, executed_at) VALUES (:migration, :batch, CURRENT_TIMESTAMP)'
                );
                $statement->execute([
                    'migration' => $migration,
                    'batch' => $batch,
                ]);
                if ($this->connection->inTransaction()) {
                    $this->connection->commit();
                }
                $executed[] = $migration;
            } catch (\Throwable $exception) {
                if ($this->connection->inTransaction()) {
                    $this->connection->rollBack();
                }
                throw $exception;
            }
        }

        return $executed;
    }

    private function ensureMigrationsTable(): void
    {
        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS migrations (' .
            'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,' .
            'migration VARCHAR(190) NOT NULL UNIQUE,' .
            'batch INT UNSIGNED NOT NULL,' .
            'executed_at DATETIME NOT NULL' .
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}