<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Services;

use App\Core\Database;
use PDO;

class CatalogService
{
    public function brands(): array
    {
        return Database::connection()->query('SELECT name FROM brands ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function categories(): array
    {
        return Database::connection()->query("SELECT c.id, c.name, c.slug, c.description, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.status = 'published' WHERE c.status = 'published' GROUP BY c.id, c.name, c.slug, c.description ORDER BY c.name")->fetchAll();
    }

    public function products(?string $search = null, ?string $category = null): array
    {
        $conditions = ["p.status = 'published'"];
        $parameters = [];
        if ($search !== null && trim($search) !== '') {
            $conditions[] = '(p.name LIKE :search_name OR p.short_description LIKE :search_description)';
            $searchValue = '%' . trim($search) . '%';
            $parameters['search_name'] = $searchValue;
            $parameters['search_description'] = $searchValue;
        }
        if ($category !== null && trim($category) !== '') {
            $conditions[] = 'c.slug = :category';
            $parameters['category'] = trim($category);
        }
        $statement = Database::connection()->prepare(
            'SELECT p.id, p.uuid, p.name, p.slug, p.short_description, p.requires_prescription, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name FROM products p INNER JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id WHERE ' . implode(' AND ', $conditions) . ' ORDER BY p.name'
        );
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name FROM products p INNER JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id WHERE p.slug = :slug AND p.status = 'published' LIMIT 1"
        );
        $statement->execute(['slug' => $slug]);
        $product = $statement->fetch();
        return is_array($product) ? $product : null;
    }

    public function createCategory(array $data): void
    {
        $statement = Database::connection()->prepare('INSERT INTO categories (name, slug, description, status, created_at, updated_at) VALUES (:name, :slug, :description, :status, NOW(), NOW())');
        $statement->execute([
            'name' => trim($data['name']),
            'slug' => $this->slug($data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'status' => 'published',
        ]);
    }

    public function createProduct(array $data): void
    {
        $categoryStatement = Database::connection()->prepare('SELECT id FROM categories WHERE slug = :slug AND status = \'published\' LIMIT 1');
        $categoryStatement->execute(['slug' => $data['category_slug']]);
        $categoryId = $categoryStatement->fetchColumn();
        if ($categoryId === false) {
            throw new \InvalidArgumentException('Categorie introuvable.');
        }
        $statement = Database::connection()->prepare('INSERT INTO products (uuid, category_id, name, slug, short_description, description, requires_prescription, status, created_at, updated_at) VALUES (:uuid, :category_id, :name, :slug, :short_description, :description, :requires_prescription, :status, NOW(), NOW())');
        $statement->execute([
            'uuid' => $this->uuid(),
            'category_id' => (int) $categoryId,
            'name' => trim($data['name']),
            'slug' => $this->slug($data['name']),
            'short_description' => trim((string) ($data['short_description'] ?? '')) ?: null,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'requires_prescription' => ! empty($data['requires_prescription']) ? 1 : 0,
            'status' => 'published',
        ]);
    }

    private function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($value)) ?: trim($value);
        $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?? 'produit');
        return trim($value, '-') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}