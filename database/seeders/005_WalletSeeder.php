<?php

declare(strict_types=1);

/**
 * Seeder: Wallets, Transactions, Topups
 */

return [
    'run' => function (\PDO $pdo): void {
        // Create wallets for all users who don't have one
        $pdo->exec("
            INSERT IGNORE INTO wallets (user_id, currency, balance, available_balance, total_received, total_spent, status, created_at)
            SELECT id, 'USD', 0, 0, 0, 0, 'active', created_at FROM users
        ");

        $wallets = $pdo->query("SELECT id, user_id FROM wallets")->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($wallets)) return;

        $insertTx = $pdo->prepare("
            INSERT INTO wallet_transactions (wallet_id, transaction_type, amount, balance_before, balance_after, payment_method, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'completed', ?)
        ");

        $insertTopup = $pdo->prepare("
            INSERT INTO wallet_topups (wallet_id, amount, payment_method, external_transaction_id, status, created_at, completed_at)
            VALUES (?, ?, ?, ?, 'completed', ?, ?)
        ");

        $insertHistory = $pdo->prepare("
            INSERT INTO wallet_balance_history (wallet_id, balance, reserved_balance, available_balance, snapshot_date)
            VALUES (?, ?, 0, ?, ?)
        ");

        $updateWallet = $pdo->prepare("
            UPDATE wallets SET balance = ?, available_balance = ?, total_received = ?, total_spent = ? WHERE id = ?
        ");

        $topupMethods = ['card', 'mobile_money', 'bank_transfer'];

        foreach ($wallets as $w) {
            $balance = 0;
            $totalReceived = 0;
            $totalSpent = 0;

            // 2-5 topups per wallet
            $numTopups = rand(2, 5);
            for ($t = 0; $t < $numTopups; $t++) {
                $amount = [5000, 10000, 20000, 50000, 100000][array_rand([5000, 10000, 20000, 50000, 100000])];
                $method = $topupMethods[array_rand($topupMethods)];
                $daysAgo = rand(5, 90);
                $date = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
                $extRef = 'TX-' . strtoupper(substr(md5((string) mt_rand()), 0, 12));

                $balBefore = $balance;
                $balance += $amount;
                $totalReceived += $amount;

                $insertTx->execute([$w['id'], 'credit', $amount, $balBefore, $balance, $method, "Rechargement {$method}", $date]);
                $insertTopup->execute([$w['id'], $amount, $method, $extRef, $date, $date]);
            }

            // 0-3 debits (purchases)
            $numDebits = rand(0, 3);
            for ($d = 0; $d < $numDebits; $d++) {
                $amount = rand(2000, min(30000, (int) $balance));
                if ($amount <= 0) break;
                $daysAgo = rand(1, 30);
                $date = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));

                $balBefore = $balance;
                $balance -= $amount;
                $totalSpent += $amount;

                $insertTx->execute([$w['id'], 'debit', $amount, $balBefore, $balance, 'wallet', 'Paiement commande', $date]);
            }

            // Update final balance
            $updateWallet->execute([$balance, $balance, $totalReceived, $totalSpent, $w['id']]);

            // Balance history snapshot
            $insertHistory->execute([$w['id'], $balance, $balance, date('Y-m-d H:i:s')]);
        }
    },
];
