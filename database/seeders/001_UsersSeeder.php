<?php

declare(strict_types=1);

/**
 * Seeder: Sample Users (admin, moderator, merchants, customers, deliverers)
 */

return [
    'run' => function (\PDO $pdo): void {
        $hash = password_hash('Password123!', PASSWORD_BCRYPT);

        $users = [
            // [email, phone, username, first_name, last_name, status, role]
            ['admin@afiazone.com',     '+243999000001', 'admin',        'Admin',     'AfiaZone',   'active', 'admin'],
            ['moderator@afiazone.com', '+243999000002', 'moderator',    'Modeste',   'Kabongo',    'active', 'moderator'],
            ['pharma1@afiazone.com',   '+243999000003', 'pharma1',      'Jean',      'Mutombo',    'active', 'merchant'],
            ['pharma2@afiazone.com',   '+243999000004', 'pharma2',      'Marie',     'Lukusa',     'active', 'merchant'],
            ['pharma3@afiazone.com',   '+243999000005', 'pharma3',      'Patrick',   'Ilunga',     'active', 'merchant'],
            ['client1@example.com',    '+243999000006', 'sophie.k',     'Sophie',    'Kalala',     'active', 'customer'],
            ['client2@example.com',    '+243999000007', 'david.m',      'David',     'Mbuyi',      'active', 'customer'],
            ['client3@example.com',    '+243999000008', 'amina.t',      'Amina',     'Tshimanga',  'active', 'customer'],
            ['client4@example.com',    '+243999000009', 'pierre.k',     'Pierre',    'Kabila',     'active', 'customer'],
            ['client5@example.com',    '+243999000010', 'grace.n',      'Grace',     'Nzuzi',      'active', 'customer'],
            ['livreur1@afiazone.com',  '+243999000011', 'jacques.kas',  'Jacques',   'Kasongo',    'active', 'deliverer'],
            ['livreur2@afiazone.com',  '+243999000012', 'emmanuel.m',   'Emmanuel',  'Mwamba',     'active', 'deliverer'],
            ['livreur3@afiazone.com',  '+243999000013', 'fidele.n',     'Fidèle',    'Ngoy',       'active', 'deliverer'],
            ['partner1@example.com',   '+243999000014', 'sonas.assur',  'SONAS',     'Assurance',  'active', 'partner'],
        ];

        $insertUser = $pdo->prepare("
            INSERT INTO users (email, phone, username, unique_id, password_hash, first_name, last_name, status, email_verified_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW() - INTERVAL FLOOR(RAND()*90) DAY)
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

        foreach ($users as [$email, $phone, $username, $firstName, $lastName, $status, $role]) {
            // Generate a unique UUID for each user
            $uniqueId = sprintf(
                '%08x-%04x-%04x-%04x-%08x%04x',
                mt_rand(0, 0xffffffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffffffff),
                mt_rand(0, 0xffff)
            );

            $insertUser->execute([$email, $phone, $username, $uniqueId, $hash, $firstName, $lastName, $status]);

            $uid = $pdo->query("SELECT id FROM users WHERE email = " . $pdo->quote($email))->fetchColumn();
            if (!$uid) continue;

            $insertRole->execute([$uid, $role]);
            $insertProfile->execute([$uid, 'RD Congo', $cities[array_rand($cities)]]);
        }
    },
];
