<?php

declare(strict_types=1);

/**
 * Seeder: Sample Users (admin, moderator, merchants, customers, deliverers)
 */

return [
    'run' => function (\PDO $pdo): void {
        $hash = password_hash('Password123!', PASSWORD_BCRYPT);

        $users = [
            // [email, first_name, last_name, status, role]
            ['admin@afiazone.com',     'Admin',    'AfiaZone',  'active', 'admin'],
            ['moderator@afiazone.com', 'Modeste',  'Kabongo',   'active', 'moderator'],
            ['pharma1@afiazone.com',   'Jean',     'Mutombo',   'active', 'merchant'],
            ['pharma2@afiazone.com',   'Marie',    'Lukusa',    'active', 'merchant'],
            ['pharma3@afiazone.com',   'Patrick',  'Ilunga',    'active', 'merchant'],
            ['client1@example.com',    'Sophie',   'Kalala',    'active', 'customer'],
            ['client2@example.com',    'David',    'Mbuyi',     'active', 'customer'],
            ['client3@example.com',    'Amina',    'Tshimanga', 'active', 'customer'],
            ['client4@example.com',    'Pierre',   'Kabila',    'active', 'customer'],
            ['client5@example.com',    'Grace',    'Nzuzi',     'active', 'customer'],
            ['livreur1@afiazone.com',  'Jacques',  'Kasongo',   'active', 'deliverer'],
            ['livreur2@afiazone.com',  'Emmanuel', 'Mwamba',    'active', 'deliverer'],
            ['livreur3@afiazone.com',  'Fidèle',   'Ngoy',      'active', 'deliverer'],
            ['partner1@example.com',   'SONAS',    'Assurance', 'active', 'partner'],
        ];

        $insertUser = $pdo->prepare("
            INSERT INTO users (email, password_hash, first_name, last_name, status, email_verified_at, created_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW() - INTERVAL FLOOR(RAND()*90) DAY)
            ON DUPLICATE KEY UPDATE email=email
        ");

        $insertRole = $pdo->prepare("
            INSERT IGNORE INTO user_roles (user_id, role_id)
            SELECT ?, id FROM roles WHERE name = ?
        ");

        $insertProfile = $pdo->prepare("
            INSERT IGNORE INTO user_profiles (user_id, country, city, preferred_locale)
            VALUES (?, ?, ?, 'fr')
        ");

        $cities = ['Kinshasa', 'Lubumbashi', 'Mbuji-Mayi', 'Kisangani', 'Goma', 'Bukavu', 'Kananga'];

        foreach ($users as [$email, $firstName, $lastName, $status, $role]) {
            $insertUser->execute([$email, $hash, $firstName, $lastName, $status]);

            $uid = $pdo->query("SELECT id FROM users WHERE email = " . $pdo->quote($email))->fetchColumn();
            if (!$uid) continue;

            $insertRole->execute([$uid, $role]);
            $insertProfile->execute([$uid, 'RD Congo', $cities[array_rand($cities)]]);
        }
    },
];
