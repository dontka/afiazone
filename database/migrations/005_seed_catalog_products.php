<?php

declare(strict_types=1);

return static function (PDO $connection): void {
    $products = [
        ['Paracetamol 500mg', 'medicaments-pharmaceutiques', 'Antalgique courant', 0],
        ['Amoxicilline 500mg', 'medicaments-pharmaceutiques', 'Antibiotique a prescription', 1],
        ['Test rapide paludisme', 'produits-diagnostic', 'Autotest rapide', 0],
        ['Tensiometre digital', 'dispositifs-medicaux', 'Mesure de la tension arterielle', 0],
        ['Gants medicaux nitrile', 'dispositifs-medicaux', 'Protection non sterile', 0],
        ['Compresses steriles', 'soins-pansements', 'Pansement et soins des plaies', 0],
        ['Gel hydroalcoolique', 'antiseptiques-desinfectants', 'Desinfection des mains', 0],
        ['Vitamine C 1000mg', 'nutrition-medicale', 'Complement nutritionnel', 0],
    ];
    $categoryStatement = $connection->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
    $productStatement = $connection->prepare(
        'INSERT IGNORE INTO products (uuid, category_id, name, slug, short_description, description, requires_prescription, status, created_at, updated_at) VALUES (:uuid, :category_id, :name, :slug, :short_description, :description, :requires_prescription, "published", NOW(), NOW())'
    );
    foreach ($products as [$name, $categorySlug, $description, $requiresPrescription]) {
        $categoryStatement->execute(['slug' => $categorySlug]);
        $categoryId = $categoryStatement->fetchColumn();
        if ($categoryId === false) {
            continue;
        }
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name), '-'));
        $productStatement->execute([
            'uuid' => sprintf('%s%s-%s-%s-%s-%s%s%s', ...str_split(bin2hex(random_bytes(16)), 4)),
            'category_id' => (int) $categoryId,
            'name' => $name,
            'slug' => $slug,
            'short_description' => $description,
            'description' => 'Fiche produit canonique AfiaZone. Les informations complementaires seront validees par un professionnel habilite.',
            'requires_prescription' => $requiresPrescription,
        ]);
    }
};