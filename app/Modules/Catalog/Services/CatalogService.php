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

    public function brandOptions(): array
    {
        return Database::connection()->query('SELECT id, name FROM brands ORDER BY name')->fetchAll();
    }

    public function ingredients(): array
    {
        return Database::connection()->query('SELECT id, name FROM active_ingredients ORDER BY name')->fetchAll();
    }

    public function categories(bool $includeUnpublished = false): array
    {
        $where = $includeUnpublished ? '' : "WHERE c.status = 'published'";
        $statement = Database::connection()->query(
            "SELECT c.id, c.parent_id, c.name, c.slug, c.description, c.status, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.status = 'published' {$where} GROUP BY c.id, c.parent_id, c.name, c.slug, c.description, c.status ORDER BY c.name"
        );
        return $statement->fetchAll();
    }

    public function products(?string $search = null, ?string $category = null): array
    {
        $conditions = ["p.status = 'published'", "c.status = 'published'"];
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

    public function adminProducts(): array
    {
        return Database::connection()->query(
            'SELECT p.id, p.uuid, p.name, p.slug, p.short_description, p.description, p.requires_prescription, p.status, p.category_id, p.brand_id, c.name AS category_name, b.name AS brand_name FROM products p INNER JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id ORDER BY p.updated_at DESC, p.name'
        )->fetchAll();
    }

    public function publishedProductCount(): int
    {
        return (int) Database::connection()->query("SELECT COUNT(*) FROM products WHERE status = 'published'")->fetchColumn();
    }

    public function brandCount(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM brands')->fetchColumn();
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name FROM products p INNER JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id WHERE p.slug = :slug AND p.status = 'published' AND c.status = 'published' LIMIT 1"
        );
        $statement->execute(['slug' => $slug]);
        $product = $statement->fetch();
        if (is_array($product)) {
            $this->attachProductRelations($product);
        }
        return is_array($product) ? $product : null;
    }

    public function findAdminProduct(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name FROM products p INNER JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id WHERE p.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();
        if (! is_array($product)) {
            return null;
        }
        $this->attachProductRelations($product);
        return $product;
    }

    public function findCategory(string $slug, bool $includeUnpublished = false): ?array
    {
        $where = $includeUnpublished ? '' : " AND c.status = 'published'";
        $statement = Database::connection()->prepare("SELECT c.* FROM categories c WHERE c.slug = :slug{$where} LIMIT 1");
        $statement->execute(['slug' => $slug]);
        $category = $statement->fetch();
        return is_array($category) ? $category : null;
    }

    public function createCategory(array $data): void
    {
        $statement = Database::connection()->prepare('INSERT INTO categories (parent_id, name, slug, description, status, created_at, updated_at) VALUES (:parent_id, :name, :slug, :description, :status, NOW(), NOW())');
        $statement->execute([
            'parent_id' => $this->nullableId($data['parent_id'] ?? null),
            'name' => trim($data['name']),
            'slug' => $this->slug($data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'status' => $data['status'] ?? 'draft',
        ]);
    }

    public function updateCategory(int $id, array $data): void
    {
        $statement = Database::connection()->prepare('UPDATE categories SET parent_id = :parent_id, name = :name, description = :description, status = :status, updated_at = NOW() WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'parent_id' => $this->nullableId($data['parent_id'] ?? null),
            'name' => trim($data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'status' => $data['status'],
        ]);
    }

    public function deleteCategory(int $id): void
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
        $statement->execute(['id' => $id]);
            if ((int) $statement->fetchColumn() > 0) {
                throw new \RuntimeException('Une categorie utilisee par des produits ne peut pas etre supprimee.');
        }
        $statement = Database::connection()->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function createProduct(array $data): void
    {
        $categoryStatement = Database::connection()->prepare('SELECT id FROM categories WHERE slug = :slug AND status = \'published\' LIMIT 1');
        $categoryStatement->execute(['slug' => $data['category_slug']]);
        $categoryId = $categoryStatement->fetchColumn();
        if ($categoryId === false) {
            throw new \InvalidArgumentException('Categorie introuvable.');
        }
        $statement = Database::connection()->prepare('INSERT INTO products (uuid, category_id, brand_id, name, slug, short_description, description, requires_prescription, status, created_at, updated_at) VALUES (:uuid, :category_id, :brand_id, :name, :slug, :short_description, :description, :requires_prescription, :status, NOW(), NOW())');
        $statement->execute([
            'uuid' => $this->uuid(),
            'category_id' => (int) $categoryId,
            'brand_id' => $this->nullableId($data['brand_id'] ?? null),
            'name' => trim($data['name']),
            'slug' => $this->slug($data['name']),
            'short_description' => trim((string) ($data['short_description'] ?? '')) ?: null,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'requires_prescription' => ! empty($data['requires_prescription']) ? 1 : 0,
            'status' => $data['status'] ?? 'draft',
        ]);
        $this->syncIngredients((int) Database::connection()->lastInsertId(), $data['ingredient_ids'] ?? []);
        $this->syncVariants((int) Database::connection()->lastInsertId(), $data['variants'] ?? []);
    }

    public function updateProduct(int $id, array $data): void
    {
        $statement = Database::connection()->prepare('UPDATE products SET category_id = :category_id, brand_id = :brand_id, name = :name, short_description = :short_description, description = :description, requires_prescription = :requires_prescription, status = :status, updated_at = NOW() WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'category_id' => $this->requiredId($data['category_id'] ?? null, 'Categorie'),
            'brand_id' => $this->nullableId($data['brand_id'] ?? null),
            'name' => trim($data['name']),
            'short_description' => trim((string) ($data['short_description'] ?? '')) ?: null,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'requires_prescription' => ! empty($data['requires_prescription']) ? 1 : 0,
            'status' => $data['status'],
        ]);
        $this->syncIngredients($id, $data['ingredient_ids'] ?? []);
        $this->syncVariants($id, $data['variants'] ?? []);
    }

    public function changeProductStatus(int $id, string $status): void
    {
        if (! in_array($status, ['draft', 'pending_review', 'published', 'archived'], true)) {
              throw new \InvalidArgumentException('Statut de produit invalide.');
        }
        $statement = Database::connection()->prepare('UPDATE products SET status = :status, updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id, 'status' => $status]);
    }

    public function deleteProduct(int $id): array
    {
        $statement = Database::connection()->prepare('SELECT path FROM product_images WHERE product_id = :id UNION ALL SELECT path FROM product_documents WHERE product_id = :id');
        $statement->execute(['id' => $id]);
        $paths = $statement->fetchAll(PDO::FETCH_COLUMN);
        $statement = Database::connection()->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
        return array_values(array_filter($paths, 'is_string'));
    }

    public function addImage(int $productId, string $path, ?string $altText, int $sortOrder = 0): void
    {
        $statement = Database::connection()->prepare('INSERT INTO product_images (product_id, path, alt_text, sort_order, created_at) VALUES (:product_id, :path, :alt_text, :sort_order, NOW())');
        $statement->execute(['product_id' => $productId, 'path' => $path, 'alt_text' => $altText, 'sort_order' => $sortOrder]);
    }

    public function addDocument(int $productId, string $path, string $documentType): void
    {
        $statement = Database::connection()->prepare('INSERT INTO product_documents (product_id, path, document_type, created_at) VALUES (:product_id, :path, :document_type, NOW())');
        $statement->execute(['product_id' => $productId, 'path' => $path, 'document_type' => $documentType]);
    }

    public function deleteImage(int $id): ?string
    {
        return $this->deleteMedia('product_images', $id);
    }

    public function deleteDocument(int $id): ?string
    {
        return $this->deleteMedia('product_documents', $id);
    }

    public function publicImage(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT pi.path FROM product_images pi INNER JOIN products p ON p.id = pi.product_id INNER JOIN categories c ON c.id = p.category_id WHERE pi.id = :id AND p.status = 'published' AND c.status = 'published' LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $image = $statement->fetch();
        return is_array($image) ? $image : null;
    }

    private function deleteMedia(string $table, int $id): ?string
    {
        $statement = Database::connection()->prepare("SELECT path FROM {$table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        $path = $statement->fetchColumn();
        $statement = Database::connection()->prepare("DELETE FROM {$table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        return is_string($path) ? $path : null;
    }

    private function attachProductRelations(array &$product): void
    {
        $statement = Database::connection()->prepare('SELECT id, name, sku FROM product_variants WHERE product_id = :id ORDER BY name');
        $statement->execute(['id' => $product['id']]);
        $product['variants'] = $statement->fetchAll();
        $statement = Database::connection()->prepare('SELECT ai.id, ai.name FROM active_ingredients ai INNER JOIN product_active_ingredients pai ON pai.active_ingredient_id = ai.id WHERE pai.product_id = :id ORDER BY ai.name');
        $statement->execute(['id' => $product['id']]);
        $product['ingredients'] = $statement->fetchAll();
        $statement = Database::connection()->prepare('SELECT id, path, alt_text, sort_order FROM product_images WHERE product_id = :id ORDER BY sort_order, id');
        $statement->execute(['id' => $product['id']]);
        $product['images'] = $statement->fetchAll();
        $statement = Database::connection()->prepare('SELECT id, path, document_type FROM product_documents WHERE product_id = :id ORDER BY id');
        $statement->execute(['id' => $product['id']]);
        $product['documents'] = $statement->fetchAll();
    }

    private function syncIngredients(int $productId, array $ingredientIds): void
    {
        $connection = Database::connection();
        $connection->prepare('DELETE FROM product_active_ingredients WHERE product_id = :id')->execute(['id' => $productId]);
        $statement = $connection->prepare('INSERT INTO product_active_ingredients (product_id, active_ingredient_id) SELECT :product_id, id FROM active_ingredients WHERE id = :ingredient_id');
        foreach (array_unique(array_map('intval', $ingredientIds)) as $ingredientId) {
            if ($ingredientId > 0) {
                $statement->execute(['product_id' => $productId, 'ingredient_id' => $ingredientId]);
            }
        }
    }

    private function syncVariants(int $productId, array $variants): void
    {
        $connection = Database::connection();
        $connection->prepare('DELETE FROM product_variants WHERE product_id = :id')->execute(['id' => $productId]);
        $statement = $connection->prepare('INSERT INTO product_variants (product_id, name, sku, created_at, updated_at) VALUES (:product_id, :name, :sku, NOW(), NOW())');
        foreach ($variants as $variant) {
            $name = trim((string) ($variant['name'] ?? ''));
            if ($name !== '') {
                $statement->execute(['product_id' => $productId, 'name' => $name, 'sku' => trim((string) ($variant['sku'] ?? '')) ?: null]);
            }
        }
    }

    private function nullableId(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : $this->requiredId($value, 'Reference');
    }

    private function requiredId(mixed $value, string $label): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
                throw new \InvalidArgumentException($label . ' invalide.');
        }
        return (int) $id;
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