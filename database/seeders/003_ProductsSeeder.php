<?php

declare(strict_types=1);

/**
 * Seeder: Product Categories, Products, Images, Variants
 */

return [
    'run' => function (\PDO $pdo): void {
        // Categories
        $pdo->exec("
            INSERT INTO product_categories (name, slug, description, is_active) VALUES
            ('Médicaments', 'medicaments', 'Produits pharmaceutiques', TRUE),
            ('Dispositifs Médicaux', 'dispositifs-medicaux', 'Équipements médicaux', TRUE),
            ('Vitamines & Suppléments', 'vitamines-supplements', 'Vitamines et compléments nutritionnels', TRUE),
            ('Soins & Pansements', 'soins-pansements', 'Produits de soin et pansements', TRUE),
            ('Équipement Médical', 'equipement-medical', 'Équipement et appareils médicaux', TRUE)
            ON DUPLICATE KEY UPDATE slug=slug
        ");

        // Get merchant IDs
        $merchantIds = $pdo->query("SELECT id FROM merchants ORDER BY id")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($merchantIds)) return;

        // Get category IDs
        $catIds = $pdo->query("SELECT id FROM product_categories ORDER BY id")->fetchAll(\PDO::FETCH_COLUMN);

        // Products
        $products = [
            // [name, sku, category_idx, price, cost, prescription_required, description]
            ['Paracétamol 500mg (boîte de 20)',     'MED-PARA-500',   0, 3500,   2000,  false, 'Antalgique et antipyrétique pour le traitement des douleurs et fièvres'],
            ['Amoxicilline 500mg (boîte de 12)',    'MED-AMOX-500',   0, 8500,   5200,  true,  'Antibiotique à large spectre pour infections bactériennes'],
            ['Ibuprofène 400mg (boîte de 30)',      'MED-IBUP-400',   0, 5200,   3100,  false, 'Anti-inflammatoire non stéroïdien (AINS)'],
            ['Oméprazole 20mg (boîte de 14)',       'MED-OMEP-20',    0, 6800,   4000,  false, 'Inhibiteur de la pompe à protons pour reflux gastrique'],
            ['Métronidazole 250mg (boîte de 20)',   'MED-METR-250',   0, 4200,   2500,  true,  'Antiparasitaire et antibactérien'],
            ['Chloroquine 250mg (boîte de 30)',     'MED-CHLO-250',   0, 3800,   2200,  true,  'Antipaludéen de première intention'],
            ['Artemether/Luméfantrine (12 cp)',     'MED-ARLU-20',    0, 12500,  8000,  true,  'Combinaison thérapeutique anti-paludisme (ACT)'],
            ['Thermomètre digital',                  'DM-THERM-001',   1, 8000,   4500,  false, 'Thermomètre digital haute précision avec affichage LCD'],
            ['Tensiomètre électronique bras',        'DM-TENS-001',    1, 35000,  22000, false, 'Tensiomètre automatique avec détection d\'arythmie'],
            ['Oxymètre de pouls',                    'DM-OXYM-001',    1, 25000,  15000, false, 'Mesure SpO2 et fréquence cardiaque, écran OLED'],
            ['Glucomètre + 50 bandelettes',          'DM-GLUC-001',    1, 42000,  28000, false, 'Kit complet pour autosurveillance glycémique'],
            ['Vitamine C 1000mg (boîte de 30)',     'VS-VITC-1000',   2, 7500,   4200,  false, 'Vitamine C effervescente pour renforcer l\'immunité'],
            ['Vitamine D3 1000 UI (90 gélules)',    'VS-VITD-1000',   2, 12000,  7500,  false, 'Vitamine D3 pour la santé osseuse'],
            ['Multivitamines Adulte (60 cp)',        'VS-MULT-ADULT',  2, 15000,  9000,  false, 'Complexe multivitaminé complet pour adultes'],
            ['Fer + Acide Folique (30 cp)',          'VS-FERF-30',     2, 6500,   3800,  false, 'Complément pour les femmes enceintes et anémiées'],
            ['Zinc 15mg (30 comprimés)',            'VS-ZINC-15',     2, 5000,   2800,  false, 'Oligo-élément essentiel pour l\'immunité'],
            ['Sparadrap 5m × 2cm',                   'SP-SPAR-5M',     3, 2500,   1200,  false, 'Sparadrap hypoallergénique microporeux'],
            ['Compresses stériles 10×10 (boîte 50)', 'SP-COMP-10',    3, 8000,   4500,  false, 'Compresses de gaze stériles non tissées'],
            ['Bande de gaze 5m × 10cm',             'SP-BAND-5M',     3, 3000,   1500,  false, 'Bande de gaze extensible pour pansement'],
            ['Antiseptique Bétadine 125ml',          'SP-BETA-125',    3, 9500,   5800,  false, 'Solution antiseptique à base de povidone iodée'],
            ['Fauteuil roulant pliable',             'EQ-FAUT-001',    4, 350000, 220000,false, 'Fauteuil roulant manuel pliable, structure acier chromé'],
            ['Nébuliseur ultrasonique',              'EQ-NEBU-001',    4, 65000,  40000, false, 'Nébuliseur pour le traitement de l\'asthme et bronchites'],
            ['Lit médical 2 manivelles',            'EQ-LIT-002',     4, 850000, 550000,false, 'Lit hospitalier ajustable avec matelas anti-escarres'],
            ['Concentrateur d\'oxygène 5L',          'EQ-OXY-005',     4, 750000, 480000,false, 'Concentrateur d\'oxygène portable 5 litres/minute'],
            ['Stéthoscope Littmann Classic III',    'EQ-STETH-003',   4, 120000, 75000, false, 'Stéthoscope professionnel double pavillon'],
        ];

        $insertProduct = $pdo->prepare("
            INSERT INTO products (merchant_id, sku, name, slug, description, category_id, price, cost_price, tax_rate, prescription_required, is_active, is_featured, rating, review_count, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 16.00, ?, TRUE, ?, ?, ?, 'published', NOW() - INTERVAL ? DAY)
            ON DUPLICATE KEY UPDATE sku=sku
        ");

        $insertImage = $pdo->prepare("
            INSERT INTO product_images (product_id, image_url, alt_text, is_primary, display_order, created_at)
            VALUES (?, ?, ?, TRUE, 1, NOW())
        ");

        foreach ($products as $i => $p) {
            [$name, $sku, $catIdx, $price, $cost, $rx, $desc] = $p;
            $merchantId = $merchantIds[$i % count($merchantIds)];
            $catId = $catIds[$catIdx] ?? $catIds[0];
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $featured = ($i < 5) ? 1 : 0;
            $rating = round(3.0 + (mt_rand(0, 20) / 10), 2);
            $reviewCount = rand(0, 50);
            $days = rand(1, 180);

            $insertProduct->execute([
                $merchantId, $sku, $name, $slug, $desc, $catId,
                $price, $cost, (int) $rx, $featured, $rating, $reviewCount, $days
            ]);

            $productId = $pdo->query("SELECT id FROM products WHERE sku = " . $pdo->quote($sku))->fetchColumn();
            if ($productId) {
                $insertImage->execute([$productId, "/assets/img/products/placeholder.png", $name]);
            }
        }

        // Variants for some products (different dosages/sizes)
        $productVariants = [
            'MED-PARA-500' => [
                ['PAR-1000', 'Paracétamol 1000mg', 5500, 20],
                ['PAR-EFFERV', 'Paracétamol Effervescent', 4200, 15],
            ],
            'VS-VITC-1000' => [
                ['VITC-500', 'Vitamine C 500mg', 4500, 50],
                ['VITC-GOMMES', 'Vitamine C Gommes (enfant)', 6000, 30],
            ],
        ];

        $insertVariant = $pdo->prepare("
            INSERT INTO product_variants (product_id, sku_suffix, variant_name, variant_price, stock_quantity, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        foreach ($productVariants as $parentSku => $variants) {
            $pid = $pdo->query("SELECT id FROM products WHERE sku = " . $pdo->quote($parentSku))->fetchColumn();
            if (!$pid) continue;
            foreach ($variants as $v) {
                $insertVariant->execute([$pid, $v[0], $v[1], $v[2], $v[3]]);
            }
        }

        // Merchant stocks
        $stockStmt = $pdo->prepare("
            INSERT IGNORE INTO merchant_stocks (merchant_id, product_id, quantity, reorder_level, last_restock_date)
            VALUES (?, ?, ?, ?, NOW() - INTERVAL ? DAY)
        ");

        $allProducts = $pdo->query("SELECT id, merchant_id FROM products")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($allProducts as $prod) {
            $stockStmt->execute([
                $prod['merchant_id'],
                $prod['id'],
                rand(5, 200),
                rand(5, 20),
                rand(1, 30)
            ]);
        }
    },
];
