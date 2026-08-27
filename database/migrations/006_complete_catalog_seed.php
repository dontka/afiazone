<?php

declare(strict_types=1);

return static function (PDO $connection): void {
    $categories = [
        ['Antalgiques et antipyrétiques', 'antalgiques-antipyretiques', 'medicaments-pharmaceutiques'],
        ['Antibiotiques', 'antibiotiques', 'medicaments-pharmaceutiques'],
        ['Pansements', 'pansements', 'soins-pansements'],
        ['Vitamines et mineraux', 'vitamines-mineraux', 'nutrition-medicale'],
    ];
    $categoryStatement = $connection->prepare(
        'INSERT IGNORE INTO categories (parent_id, name, slug, description, status, created_at, updated_at) SELECT id, :name, :slug, :description, "published", NOW(), NOW() FROM categories WHERE slug = :parent_slug LIMIT 1'
    );
    foreach ($categories as [$name, $slug, $parentSlug]) {
        $categoryStatement->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => 'Sous-categorie du catalogue medical AfiaZone.',
            'parent_slug' => $parentSlug,
        ]);
    }

    $ingredients = [
        ['Paracetamol', 'paracetamol'],
        ['Amoxicilline', 'amoxicilline'],
        ['Acide ascorbique', 'acide-ascorbique'],
    ];
    $ingredientStatement = $connection->prepare(
        'INSERT IGNORE INTO active_ingredients (name, slug, created_at, updated_at) VALUES (:name, :slug, NOW(), NOW())'
    );
    foreach ($ingredients as [$name, $slug]) {
        $ingredientStatement->execute(['name' => $name, 'slug' => $slug]);
    }

    $connection->exec(
        "UPDATE products SET brand_id = (SELECT id FROM brands WHERE name = 'AfiaCare' LIMIT 1) WHERE brand_id IS NULL"
    );
    $connection->exec(
        "INSERT IGNORE INTO product_active_ingredients (product_id, active_ingredient_id) SELECT p.id, i.id FROM products p INNER JOIN active_ingredients i ON ((p.name LIKE '%Paracetamol%' AND i.slug = 'paracetamol') OR (p.name LIKE '%Amoxicilline%' AND i.slug = 'amoxicilline') OR (p.name LIKE '%Vitamine C%' AND i.slug = 'acide-ascorbique'))"
    );
};