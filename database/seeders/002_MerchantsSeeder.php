<?php

declare(strict_types=1);

/**
 * Seeder: Merchant profiles, Tiers, Shipping info, Fees
 */

return [
    'run' => function (\PDO $pdo): void {
        // Tiers
        $pdo->exec("
            INSERT INTO merchant_tiers (name, display_name, sales_commission_percent, advertisement_fee) VALUES
            ('verified', 'Vérifié', 10.00, 50.00),
            ('premium', 'Premium', 7.50, 30.00),
            ('gold', 'Gold', 5.00, 15.00),
            ('diamond', 'Diamond', 3.00, 0.00)
            ON DUPLICATE KEY UPDATE name=name
        ");

        // Get merchant user IDs
        $merchants = $pdo->query("
            SELECT u.id, u.first_name, u.last_name
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id AND r.name = 'merchant'
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $businesses = [
            ['Pharmacie Centrale de Kinshasa', 'retailer', 1, 'Spécialiste en médicaments génériques et produits de santé'],
            ['PharmaPro Lubumbashi',           'wholesaler', 2, 'Grossiste pharmaceutique de premier plan'],
            ['MediStock Congo',                'producer',   1, 'Producteur local de dispositifs médicaux'],
        ];

        $insertMerchant = $pdo->prepare("
            INSERT INTO merchants (user_id, business_name, business_type, tier_id, description, status, rating, total_reviews, verification_date, created_at)
            VALUES (?, ?, ?, ?, ?, 'active', ?, ?, NOW() - INTERVAL ? DAY, NOW() - INTERVAL ? DAY)
            ON DUPLICATE KEY UPDATE user_id=user_id
        ");

        $insertShipping = $pdo->prepare("
            INSERT IGNORE INTO merchant_shipping_info (merchant_id, warehouse_address, warehouse_city, warehouse_country, processing_time_days, accepts_cash_on_delivery, accepts_wallet_payment)
            VALUES (?, ?, ?, 'RD Congo', ?, TRUE, TRUE)
        ");

        $insertFees = $pdo->prepare("
            INSERT IGNORE INTO merchant_fees (merchant_id, commission_percent, return_fee_percent, refund_processing_days)
            VALUES (?, ?, 5.00, 7)
        ");

        foreach ($merchants as $i => $merchant) {
            $biz = $businesses[$i] ?? $businesses[0];
            $days = rand(30, 365);
            $rating = round(3.5 + (mt_rand(0, 15) / 10), 2);
            $reviews = rand(5, 120);

            $insertMerchant->execute([
                $merchant['id'], $biz[0], $biz[1], $biz[2], $biz[3],
                $rating, $reviews, $days, $days
            ]);

            $mid = $pdo->query("SELECT id FROM merchants WHERE user_id = {$merchant['id']}")->fetchColumn();
            if (!$mid) continue;

            $addresses = ['123 Avenue Kasa-Vubu', '45 Rue de la Poste', '78 Boulevard du 30 Juin'];
            $cities = ['Kinshasa', 'Lubumbashi', 'Goma'];
            $insertShipping->execute([$mid, $addresses[$i] ?? $addresses[0], $cities[$i] ?? $cities[0], rand(1, 3)]);
            $insertFees->execute([$mid, round(5 + mt_rand(0, 50) / 10, 2)]);
        }
    },
];
