<?php

declare(strict_types=1);

namespace App\Console;

class RollbackCommand extends Command
{
    public string $name = 'rollback';
    public string $description = 'Rollback the last batch of migrations';

    public function handle(array $arguments = []): int
    {
        $this->info('Rolling back last batch...');

        $pdo = db();

        $batchStmt = $pdo->query('SELECT MAX(batch) AS last_batch FROM migrations');
        $lastBatch = (int) ($batchStmt->fetch(\PDO::FETCH_ASSOC)['last_batch'] ?? 0);

        if ($lastBatch === 0) {
            $this->info('Nothing to rollback.');
            return 0;
        }

        $stmt = $pdo->prepare('SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC');
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($migrations as $name) {
            $file = BASE_PATH . '/database/migrations/' . $name;
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && file_exists($file)) {
                $migration = require $file;
                if (is_array($migration) && isset($migration['down'])) {
                    $this->info("Rolling back: {$name}");
                    ($migration['down'])($pdo);
                }
            }
            $pdo->prepare('DELETE FROM migrations WHERE migration = ?')->execute([$name]);
            $this->success("Rolled back: {$name}");
        }

        return 0;
    }
}
