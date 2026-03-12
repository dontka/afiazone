<?php

declare(strict_types=1);

/**
 * Seeder: Product, Merchant & Delivery Reviews
 */

return [
    'run' => function (\PDO $pdo): void {
        $customers = $pdo->query("
            SELECT u.id FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id AND r.name = 'customer'
        ")->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($customers)) return;

        // ── Product Reviews ──
        $products = $pdo->query("SELECT id FROM products LIMIT 15")->fetchAll(\PDO::FETCH_COLUMN);
        $deliveredOrders = $pdo->query("SELECT id FROM orders WHERE order_status = 'delivered'")->fetchAll(\PDO::FETCH_COLUMN);

        $comments = [
            [5, 'Excellent produit', 'Très satisfait, livraison rapide et produit conforme à la description.'],
            [5, 'Parfait',           'Exactement ce dont j\'avais besoin. Je recommande.'],
            [4, 'Bon rapport qualité-prix', 'Bon produit dans l\'ensemble, emballage soigné.'],
            [4, 'Satisfaisant',      'Produit conforme, rien à redire.'],
            [3, 'Correct',           'Le produit fait le travail mais l\'emballage pourrait être amélioré.'],
            [3, 'Moyen',             'Qualité acceptable, délai de livraison un peu long.'],
            [2, 'Décevant',          'Le produit ne correspond pas tout à fait à la description.'],
            [1, 'À éviter',          'Produit endommagé à la réception. Service client à améliorer.'],
        ];

        $insertReview = $pdo->prepare("
            INSERT INTO product_reviews (product_id, user_id, order_id, rating, title, comment, is_verified_purchase, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, TRUE, 'approved', NOW() - INTERVAL ? DAY)
        ");

        foreach ($products as $pid) {
            $numReviews = rand(1, 3);
            for ($r = 0; $r < $numReviews; $r++) {
                $uid = $customers[array_rand($customers)];
                $oid = !empty($deliveredOrders) ? $deliveredOrders[array_rand($deliveredOrders)] : null;
                $c = $comments[array_rand($comments)];
                $insertReview->execute([$pid, $uid, $oid, $c[0], $c[1], $c[2], rand(1, 60)]);
            }
        }

        // Update product ratings
        $pdo->exec("
            UPDATE products p SET
                rating = (SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_id = p.id AND status = 'approved'),
                review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id AND status = 'approved')
        ");

        // ── Merchant Reviews ──
        $merchantIds = $pdo->query("SELECT id FROM merchants")->fetchAll(\PDO::FETCH_COLUMN);

        $insertMerchRev = $pdo->prepare("
            INSERT INTO merchant_reviews (merchant_id, user_id, order_id, rating, comment, is_verified_purchase, service_rating, delivery_rating, packaging_rating, created_at)
            VALUES (?, ?, ?, ?, ?, TRUE, ?, ?, ?, NOW() - INTERVAL ? DAY)
        ");

        $merchComments = [
            'Service impeccable, pharmacie très professionnelle.',
            'Bonne communication et livraison dans les temps.',
            'Produits de qualité, je recommande cette pharmacie.',
            'Emballage soigné et produits authentiques.',
            'Délai un peu long mais produit conforme.',
        ];

        foreach ($merchantIds as $mid) {
            for ($r = 0; $r < rand(2, 5); $r++) {
                $uid = $customers[array_rand($customers)];
                $oid = !empty($deliveredOrders) ? $deliveredOrders[array_rand($deliveredOrders)] : null;
                $rating = rand(3, 5);
                $insertMerchRev->execute([
                    $mid, $uid, $oid, $rating,
                    $merchComments[array_rand($merchComments)],
                    rand(3, 5), rand(3, 5), rand(3, 5),
                    rand(1, 60)
                ]);
            }
        }

        // Update merchant ratings
        $pdo->exec("
            UPDATE merchants m SET
                rating = (SELECT COALESCE(AVG(rating), 0) FROM merchant_reviews WHERE merchant_id = m.id),
                total_reviews = (SELECT COUNT(*) FROM merchant_reviews WHERE merchant_id = m.id)
        ");

        // ── Delivery Reviews ──
        $personnelIds = $pdo->query("SELECT id FROM delivery_personnel")->fetchAll(\PDO::FETCH_COLUMN);
        $shipmentIds = $pdo->query("SELECT id FROM shipments WHERE status = 'delivered'")->fetchAll(\PDO::FETCH_COLUMN);

        if (!empty($personnelIds)) {
            $insertDelRev = $pdo->prepare("
                INSERT INTO delivery_reviews (delivery_personnel_id, user_id, shipment_id, rating, comment, punctuality_rating, professionalism_rating, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW() - INTERVAL ? DAY)
            ");

            $delComments = [
                'Livreur très ponctuel et aimable.',
                'Livraison rapide, colis en bon état.',
                'Bonne communication pendant la livraison.',
                'Livreur professionnel, je recommande.',
            ];

            foreach ($personnelIds as $dpid) {
                for ($r = 0; $r < rand(2, 4); $r++) {
                    $uid = $customers[array_rand($customers)];
                    $sid = !empty($shipmentIds) ? $shipmentIds[array_rand($shipmentIds)] : null;
                    $rating = rand(3, 5);
                    $insertDelRev->execute([
                        $dpid, $uid, $sid, $rating,
                        $delComments[array_rand($delComments)],
                        rand(3, 5), rand(3, 5), rand(1, 45)
                    ]);
                }
            }

            // Update delivery personnel ratings
            $pdo->exec("
                UPDATE delivery_personnel dp SET
                    average_rating = (SELECT COALESCE(AVG(rating), 0) FROM delivery_reviews WHERE delivery_personnel_id = dp.id)
            ");
        }
    },
];
