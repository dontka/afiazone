<?php

declare(strict_types=1);

/**
 * Seeder: Orders, Cart, Order Items, Status Logs, Delivery Addresses
 */

return [
    'run' => function (\PDO $pdo): void {
        // Get customer IDs
        $customers = $pdo->query("
            SELECT u.id FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id AND r.name = 'customer'
        ")->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($customers)) return;

        // Get products
        $products = $pdo->query("SELECT id, price FROM products WHERE is_active = 1 LIMIT 25")->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($products)) return;

        $paymentMethods = ['cash_on_delivery', 'wallet', 'card', 'mobile_money'];
        $orderStatuses  = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

        // Create 20 orders spread across customers
        $insertOrder = $pdo->prepare("
            INSERT INTO orders (order_number, user_id, total_amount, tax_amount, shipping_fee, discount_amount, final_amount, payment_method, payment_status, order_status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insertItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, tax_amount, subtotal, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $insertStatusLog = $pdo->prepare("
            INSERT INTO order_status_logs (order_id, previous_status, new_status, notes, created_at)
            VALUES (?, ?, ?, ?, ?)
        ");

        $insertAddress = $pdo->prepare("
            INSERT INTO delivery_addresses (order_id, user_id, recipient_name, phone_number, street_address, city, country, is_default, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'RD Congo', TRUE, ?)
        ");

        $addresses = [
            ['123 Avenue Kasa-Vubu', 'Kinshasa', '+243812345678'],
            ['45 Rue du Commerce', 'Lubumbashi', '+243823456789'],
            ['78 Boulevard Lumumba', 'Goma', '+243834567890'],
            ['12 Avenue de la Paix', 'Bukavu', '+243845678901'],
            ['90 Rue de la Poste', 'Kisangani', '+243856789012'],
        ];

        for ($i = 1; $i <= 20; $i++) {
            $customerId = $customers[array_rand($customers)];
            $orderNum = 'AZ-' . str_pad((string) (10000 + $i), 6, '0', STR_PAD_LEFT);
            $daysAgo = rand(1, 90);
            $createdAt = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
            $updatedAt = date('Y-m-d H:i:s', strtotime("-" . max(0, $daysAgo - rand(0, 5)) . " days"));

            // Pick 1-4 products for this order
            $numItems = rand(1, 4);
            $orderProducts = array_slice($products, array_rand(range(0, count($products) - 1)), $numItems);
            if (empty($orderProducts)) $orderProducts = [$products[0]];

            $totalAmount = 0;
            $items = [];
            foreach ($orderProducts as $prod) {
                $qty = rand(1, 3);
                $price = (float) $prod['price'];
                $tax = round($price * $qty * 0.16, 2);
                $subtotal = round($price * $qty + $tax, 2);
                $totalAmount += $subtotal;
                $items[] = [$prod['id'], $qty, $price, $tax, $subtotal];
            }

            $taxAmount = round($totalAmount * 0.16 / 1.16, 2);
            $shippingFee = [0, 2000, 3500, 5000][array_rand([0, 2000, 3500, 5000])];
            $discount = ($i <= 3) ? round($totalAmount * 0.1, 2) : 0;
            $finalAmount = round($totalAmount + $shippingFee - $discount, 2);
            $payMethod = $paymentMethods[array_rand($paymentMethods)];

            $statusIdx = min(rand(0, 5), 5);
            $orderStatus = $orderStatuses[$statusIdx];
            $payStatus = ($orderStatus === 'delivered') ? 'paid'
                       : (($orderStatus === 'cancelled') ? 'failed' : 'pending');
            if ($payMethod === 'wallet' && $orderStatus !== 'cancelled') $payStatus = 'paid';

            $insertOrder->execute([
                $orderNum, $customerId, $totalAmount, $taxAmount, $shippingFee,
                $discount, $finalAmount, $payMethod, $payStatus, $orderStatus,
                $createdAt, $updatedAt
            ]);

            $orderId = (int) $pdo->lastInsertId();

            foreach ($items as $item) {
                $insertItem->execute([$orderId, $item[0], $item[1], $item[2], $item[3], $item[4], $createdAt]);
            }

            // Status log
            $insertStatusLog->execute([$orderId, null, 'pending', 'Commande créée', $createdAt]);
            if ($statusIdx > 0) {
                for ($s = 1; $s <= $statusIdx; $s++) {
                    $logDate = date('Y-m-d H:i:s', strtotime($createdAt . " +{$s} day"));
                    $insertStatusLog->execute([$orderId, $orderStatuses[$s - 1], $orderStatuses[$s], null, $logDate]);
                }
            }

            // Delivery address
            $addr = $addresses[array_rand($addresses)];
            $name = $pdo->query("SELECT CONCAT(first_name, ' ', last_name) FROM users WHERE id = {$customerId}")->fetchColumn();
            $insertAddress->execute([$orderId, $customerId, $name, $addr[2], $addr[0], $addr[1], $createdAt]);
        }

        // Shopping carts (2 active carts)
        $insertCart = $pdo->prepare("INSERT INTO shopping_carts (user_id, total_price, created_at) VALUES (?, ?, NOW())");
        $insertCartItem = $pdo->prepare("INSERT INTO shopping_cart_items (cart_id, product_id, quantity, price_at_add, created_at) VALUES (?, ?, ?, ?, NOW())");

        for ($c = 0; $c < min(2, count($customers)); $c++) {
            $total = 0;
            $cartItems = [];
            for ($j = 0; $j < rand(1, 3); $j++) {
                $p = $products[array_rand($products)];
                $qty = rand(1, 2);
                $total += (float) $p['price'] * $qty;
                $cartItems[] = [$p['id'], $qty, $p['price']];
            }
            $insertCart->execute([$customers[$c], round($total, 2)]);
            $cartId = (int) $pdo->lastInsertId();
            foreach ($cartItems as $ci) {
                $insertCartItem->execute([$cartId, $ci[0], $ci[1], $ci[2]]);
            }
        }
    },
];
