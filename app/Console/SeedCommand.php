<?php

declare(strict_types=1);

namespace App\Console;

class SeedCommand extends Command
{
    public string $name = 'seed';
    public string $description = 'Seed database with sample data';

    public function handle(array $arguments = []): int
    {
        $this->info('Seeding database...');

        // TODO: Implement seeder runner

        $this->success('Database seeded!');
        return 0;
    }
}
