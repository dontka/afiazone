<?php

declare(strict_types=1);

/**
 * Seeder: Delivery Providers, Personnel, Shipments, Tracking
 */

return [
    'run' => function (\PDO $pdo): void {
        // Providers
        $pdo->exec("
            INSERT INTO delivery_providers (name, contact_phone, is_active, average_delivery_days, base_fee, per_km_fee) VALUES
            ('AfiaExpress', '+243810000001', TRUE, 2, 3000, 150),
            ('KinDelivery', '+243810000002', TRUE, 3, 2000, 100),
            ('GoLivraison', '+243810000003', TRUE, 1, 5000, 200)
            ON DUPLICATE KEY UPDATE name=name
        ");

        // Get deliverer user IDs
        $deliverers = $pdo->query("
            SELECT u.id FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id AND r.name = 'deliverer'
        ")->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($deliverers)) return;

        $providerIds = $pdo->query("SELECT id FROM delivery_providers")->fetchAll(\PDO::FETCH_COLUMN);

        // Delivery personnel
        $vehicles = [
            ['motorcycle', 'Moto', 'KIN-1234-AB'],
            ['bicycle', 'Vélo', null],
            ['car', 'Voiture', 'CD-5678-KN'],
        ];

        $insertPersonnel = $pdo->prepare("
            INSERT INTO delivery_personnel (user_id, provider_id, vehicle_type, vehicle_license_plate, is_available, average_rating, total_deliveries, created_at)
            VALUES (?, ?, ?, ?, TRUE, ?, ?, NOW() - INTERVAL ? DAY)
            ON DUPLICATE KEY UPDATE user_id=user_id
        ");

        foreach ($deliverers as $i => $uid) {
            $v = $vehicles[$i % count($vehicles)];
            $insertPersonnel->execute([
                $uid,
                $providerIds[$i % count($providerIds)],
                $v[0],
                $v[2],
                round(3.8 + mt_rand(0, 12) / 10, 2),
                rand(10, 200),
                rand(30, 180)
            ]);
        }

        // Shipments for delivered/shipped orders
        $orders = $pdo->query("
            SELECT id, order_status, created_at FROM orders
            WHERE order_status IN ('shipped','delivered')
            ORDER BY id
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $personnelIds = $pdo->query("SELECT id FROM delivery_personnel")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($personnelIds)) return;

        $insertShipment = $pdo->prepare("
            INSERT INTO shipments (order_id, tracking_number, delivery_personnel_id, provider_id, status, estimated_delivery_date, actual_delivery_date, delivery_code, signature_required, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, ?)
            ON DUPLICATE KEY UPDATE order_id=order_id
        ");

        $insertTracking = $pdo->prepare("
            INSERT INTO shipment_tracking_logs (shipment_id, status, location, notes, timestamp)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($orders as $order) {
            $trackNum = 'AZ-TRK-' . strtoupper(substr(md5((string) $order['id']), 0, 8));
            $pid = $personnelIds[array_rand($personnelIds)];
            $provId = $providerIds[array_rand($providerIds)];
            $status = ($order['order_status'] === 'delivered') ? 'delivered' : 'in_transit';
            $created = $order['created_at'];
            $estimated = date('Y-m-d H:i:s', strtotime($created . ' +3 days'));
            $actual = ($status === 'delivered') ? date('Y-m-d H:i:s', strtotime($created . ' +2 days')) : null;
            $code = str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            $insertShipment->execute([$order['id'], $trackNum, $pid, $provId, $status, $estimated, $actual, $code, $created]);
            $shipId = (int) $pdo->lastInsertId();

            // Tracking logs
            $insertTracking->execute([$shipId, 'assigned', 'Entrepôt', 'Colis assigné au livreur', $created]);
            $insertTracking->execute([$shipId, 'picked_up', 'Entrepôt', 'Colis récupéré', date('Y-m-d H:i:s', strtotime($created . ' +4 hours'))]);
            if ($status === 'delivered') {
                $insertTracking->execute([$shipId, 'in_transit', 'En route', 'Colis en cours de livraison', date('Y-m-d H:i:s', strtotime($created . ' +1 day'))]);
                $insertTracking->execute([$shipId, 'delivered', 'Destination', 'Colis livré avec succès', $actual]);
            }
        }
    },
];
