<?php

declare(strict_types=1);

/**
 * Seeder: Notifications, Support Tickets, Promotions, Languages, Ad Placements
 */

return [
    'run' => function (\PDO $pdo): void {
        // ── Languages ──
        $pdo->exec("
            INSERT INTO languages (code, name, native_name, flag_icon, is_default, is_active, is_rtl, display_order) VALUES
            ('fr', 'Français', 'Français', 'fi-fr', TRUE, TRUE, FALSE, 1),
            ('en', 'Anglais', 'English', 'fi-gb', FALSE, TRUE, FALSE, 2),
            ('sw', 'Swahili', 'Kiswahili', 'fi-tz', FALSE, TRUE, FALSE, 3)
            ON DUPLICATE KEY UPDATE code=code
        ");

        // ── Ad Placements ──
        $pdo->exec("
            INSERT INTO ad_placements (slug, name, description, dimensions, max_ads, is_active) VALUES
            ('homepage_banner', 'Bannière page d''accueil', 'Grande bannière en haut de la page d''accueil', '1200x400', 1, TRUE),
            ('category_sidebar', 'Sidebar catégorie', 'Publicité dans la sidebar des pages catégorie', '300x250', 2, TRUE),
            ('product_detail_related', 'Produit sponsorisé', 'Suggestion de produit sponsorisé sur la page détail', '300x250', 1, TRUE),
            ('search_results_top', 'Haut des résultats', 'Publicité en haut des résultats de recherche', '728x90', 1, TRUE),
            ('checkout_suggestion', 'Suggestion checkout', 'Suggestion de produit au moment du checkout', '300x250', 1, TRUE),
            ('blog_inline', 'Dans les articles', 'Publicité intégrée dans les articles de blog', '728x90', 1, TRUE)
            ON DUPLICATE KEY UPDATE slug=slug
        ");

        // ── Promotion Codes ──
        $insertPromo = $pdo->prepare("
            INSERT INTO promotion_codes (code, discount_type, discount_value, max_uses, min_order_amount, start_date, end_date, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)
            ON DUPLICATE KEY UPDATE code=code
        ");

        $insertPromo->execute(['BIENVENUE10', 'percentage', 10, 1000, 5000, '2025-01-01', '2026-12-31']);
        $insertPromo->execute(['PHARMA20',    'percentage', 20, 500,  10000, '2025-06-01', '2026-06-30']);
        $insertPromo->execute(['LIVRAISON',   'fixed_amount', 3000, 200, 15000, '2025-01-01', '2026-12-31']);
        $insertPromo->execute(['AFIAZONE50',  'fixed_amount', 5000, 100, 25000, '2026-01-01', '2026-03-31']);
        $insertPromo->execute(['SANTE2026',   'percentage', 15, 300,  8000, '2026-01-01', '2026-12-31']);

        // ── Notifications (sample) ──
        $userIds = $pdo->query("SELECT id FROM users LIMIT 10")->fetchAll(\PDO::FETCH_COLUMN);
        $insertNotif = $pdo->prepare("
            INSERT INTO notifications (user_id, notification_type, title, message, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, NOW() - INTERVAL ? DAY)
        ");

        $notifs = [
            ['order_status', 'Commande confirmée', 'Votre commande AZ-010001 a été confirmée et sera bientôt expédiée.', false],
            ['payment',      'Paiement reçu', 'Votre paiement de 35 000 FC a été reçu avec succès.', true],
            ['promotion',    'Offre spéciale !', 'Profitez de 20% de réduction avec le code PHARMA20 sur tous les médicaments.', false],
            ['system',       'Bienvenue sur AfiaZone', 'Votre compte a été créé avec succès. Complétez votre profil pour une meilleure expérience.', true],
            ['support',      'Réponse du support', 'Un agent a répondu à votre ticket #T-001. Consultez la réponse.', false],
            ['alert',        'Vérification email', 'Veuillez vérifier votre adresse email pour activer toutes les fonctionnalités.', false],
            ['order_status', 'Commande livrée', 'Votre commande AZ-010005 a été livrée. N\'oubliez pas de laisser un avis !', true],
            ['payment',      'Rechargement wallet', 'Votre portefeuille a été rechargé de 50 000 FC.', true],
        ];

        foreach ($userIds as $uid) {
            $numNotifs = rand(2, 5);
            for ($n = 0; $n < $numNotifs; $n++) {
                $no = $notifs[array_rand($notifs)];
                $insertNotif->execute([$uid, $no[0], $no[1], $no[2], (int) $no[3], rand(1, 30)]);
            }
        }

        // ── Support Tickets ──
        $customerIds = $pdo->query("
            SELECT u.id FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id AND r.name = 'customer'
        ")->fetchAll(\PDO::FETCH_COLUMN);

        $insertTicket = $pdo->prepare("
            INSERT INTO support_tickets (user_id, ticket_number, subject, description, category, priority, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW() - INTERVAL ? DAY)
            ON DUPLICATE KEY UPDATE ticket_number=ticket_number
        ");

        $insertMsg = $pdo->prepare("
            INSERT INTO support_messages (ticket_id, user_id, message, created_at)
            VALUES (?, ?, ?, NOW() - INTERVAL ? DAY)
        ");

        $tickets = [
            ['Produit endommagé à la réception', 'J\'ai reçu un thermomètre avec l\'écran fissuré. Commande AZ-010003.', 'product_issue', 'high', 'open'],
            ['Retard de livraison', 'Ma commande devait arriver il y a 3 jours, toujours pas de nouvelles.', 'shipping', 'medium', 'in_progress'],
            ['Problème de paiement wallet', 'Mon paiement par wallet a échoué mais le montant a été débité.', 'billing', 'urgent', 'open'],
            ['Comment annuler une commande ?', 'Je souhaite annuler ma commande passée ce matin.', 'general', 'low', 'resolved'],
        ];

        if (!empty($customerIds)) {
            foreach ($tickets as $i => $t) {
                $uid = $customerIds[$i % count($customerIds)];
                $ticketNum = 'T-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
                $insertTicket->execute([$uid, $ticketNum, $t[0], $t[1], $t[2], $t[3], $t[4], rand(1, 15)]);
                $ticketId = (int) $pdo->lastInsertId();
                if (!$ticketId) {
                    $ticketId = (int) $pdo->query("SELECT id FROM support_tickets WHERE ticket_number = " . $pdo->quote($ticketNum))->fetchColumn();
                }

                // Initial message (skip if ticket already had messages)
                $existingMsgs = (int) $pdo->query("SELECT COUNT(*) FROM support_messages WHERE ticket_id = {$ticketId}")->fetchColumn();
                if ($existingMsgs === 0) {
                    $insertMsg->execute([$ticketId, $uid, $t[1], rand(1, 15)]);

                    // Agent reply for in_progress/resolved
                    if ($t[4] !== 'open') {
                        $adminId = $pdo->query("SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE r.name = 'admin' LIMIT 1")->fetchColumn();
                        if ($adminId) {
                            $insertMsg->execute([$ticketId, $adminId, 'Bonjour, nous avons bien reçu votre demande. Notre équipe traite votre dossier.', rand(0, 10)]);
                        }
                    }
                }
            }
        }
    },
];
