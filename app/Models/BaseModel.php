<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Base Model Class — ActiveRecord-like pattern
 *
 * Provides query builder, CRUD, relationships, pagination, and transactions.
 */
abstract class BaseModel
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected ?\PDO $connection = null;
    protected array $fillable = [];
    protected array $attributes = [];
    protected bool $exists = false;

    // Query builder state (instance-level for chaining)
    private array $wheres = [];
    private array $bindings = [];
    private ?string $orderByClause = null;
    private ?int $limitValue = null;
    private ?int $offsetValue = null;
    private ?string $selectColumns = null;

    public function __construct(?array $attributes = null)
    {
        $this->connection = db();
        if ($attributes !== null) {
            $this->fill($attributes);
        }
    }

    // ─── Attribute handling ───────────────────────────────────────

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getConnection(): ?\PDO
    {
        return $this->connection;
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable, true) || $key === $this->primaryKey) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    // ─── Static CRUD ──────────────────────────────────────────────

    public static function find(mixed $id): ?static
    {
        $instance = new static();
        $stmt = $instance->connection->prepare(
            "SELECT * FROM `{$instance->table}` WHERE `{$instance->primaryKey}` = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            $model = new static($data);
            $model->exists = true;
            return $model;
        }
        return null;
    }

    public static function findOrFail(mixed $id): static
    {
        $model = static::find($id);
        if ($model === null) {
            throw new \App\Exceptions\NotFoundException(
                (new static())->table . " with id {$id} not found"
            );
        }
        return $model;
    }

    public static function create(array $attributes): static
    {
        $instance = new static();
        $instance->fill($attributes);
        $instance->save();
        return $instance;
    }

    public static function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $instance = new static();
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $instance->connection->query(
            "SELECT * FROM `{$instance->table}` ORDER BY `{$orderBy}` {$direction}"
        );
        $results = [];
        while ($row = $stmt->fetch()) {
            $model = new static($row);
            $model->exists = true;
            $results[] = $model;
        }
        return $results;
    }

    // ─── Instance CRUD ────────────────────────────────────────────

    public function save(): bool
    {
        if ($this->exists && isset($this->attributes[$this->primaryKey])) {
            return $this->performUpdate();
        }
        return $this->performInsert();
    }

    private function performInsert(): bool
    {
        $data = $this->getInsertableAttributes();
        if (empty($data)) {
            return false;
        }

        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));

        $sql = "INSERT INTO `{$this->table}` ({$columnList}) VALUES ({$placeholders})";
        $stmt = $this->connection->prepare($sql);

        if ($stmt->execute(array_values($data))) {
            $this->attributes[$this->primaryKey] = (int) $this->connection->lastInsertId();
            $this->exists = true;
            return true;
        }
        return false;
    }

    private function performUpdate(): bool
    {
        $data = $this->getInsertableAttributes();
        unset($data[$this->primaryKey]);
        if (empty($data)) {
            return false;
        }

        $sets = [];
        $values = [];
        foreach ($data as $col => $val) {
            $sets[] = "`{$col}` = ?";
            $values[] = $val;
        }
        $values[] = $this->attributes[$this->primaryKey];

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets)
             . " WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($values);
    }

    public function update(array $attributes = []): bool
    {
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
        return $this->performUpdate();
    }

    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute([$this->attributes[$this->primaryKey]]);
        if ($result) {
            $this->exists = false;
        }
        return $result;
    }

    private function getInsertableAttributes(): array
    {
        if (empty($this->fillable)) {
            return $this->attributes;
        }
        $data = [];
        foreach ($this->attributes as $key => $value) {
            if (in_array($key, $this->fillable, true) || $key === $this->primaryKey) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    // ─── Query builder (fluent) ───────────────────────────────────

    public static function query(): static
    {
        return new static();
    }

    public function select(string $columns = '*'): self
    {
        $this->selectColumns = $columns;
        return $this;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        if ($value === null) {
            $this->wheres[] = "`{$column}` = ?";
            $this->bindings[] = $operatorOrValue;
        } else {
            $allowed = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
            $op = strtoupper((string) $operatorOrValue);
            if (!in_array($op, $allowed, true)) {
                $op = '=';
            }
            if (in_array($op, ['IN', 'NOT IN']) && is_array($value)) {
                $placeholders = implode(', ', array_fill(0, count($value), '?'));
                $this->wheres[] = "`{$column}` {$op} ({$placeholders})";
                $this->bindings = array_merge($this->bindings, array_values($value));
            } else {
                $this->wheres[] = "`{$column}` {$op} ?";
                $this->bindings[] = $value;
            }
        }
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = "`{$column}` IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = "`{$column}` IS NOT NULL";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderByClause = "`{$column}` {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offsetValue = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->bindings);
        $this->resetQuery();

        $results = [];
        while ($row = $stmt->fetch()) {
            $model = new static($row);
            $model->exists = true;
            $results[] = $model;
        }
        return $results;
    }

    public function first(): ?static
    {
        $this->limitValue = 1;
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $sql = $this->buildCountQuery();
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->bindings);
        $this->resetQuery();
        return (int) $stmt->fetchColumn();
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);

        // Count total
        $countSql = $this->buildCountQuery();
        $countStmt = $this->connection->prepare($countSql);
        $countStmt->execute($this->bindings);
        $total = (int) $countStmt->fetchColumn();

        // Fetch records
        $this->limitValue = $perPage;
        $this->offsetValue = ($page - 1) * $perPage;
        $items = $this->get();

        return [
            'data' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    private function buildSelectQuery(): string
    {
        $cols = $this->selectColumns ?? '*';
        $sql = "SELECT {$cols} FROM `{$this->table}`";
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        if ($this->orderByClause) {
            $sql .= ' ORDER BY ' . $this->orderByClause;
        }
        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }
        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }
        return $sql;
    }

    private function buildCountQuery(): string
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        return $sql;
    }

    private function resetQuery(): void
    {
        $this->wheres = [];
        $this->bindings = [];
        $this->orderByClause = null;
        $this->limitValue = null;
        $this->offsetValue = null;
        $this->selectColumns = null;
    }

    // ─── Static query shortcuts ───────────────────────────────────

    public static function findBy(string $column, mixed $value): ?static
    {
        return static::query()->where($column, $value)->first();
    }

    public static function findAllBy(string $column, mixed $value): array
    {
        return static::query()->where($column, $value)->get();
    }

    // ─── Relationships ────────────────────────────────────────────

    protected function hasMany(string $relatedClass, string $foreignKey, ?string $localKey = null): array
    {
        $localKey ??= $this->primaryKey;
        $localValue = $this->attributes[$localKey] ?? null;
        if ($localValue === null) {
            return [];
        }
        /** @var BaseModel $related */
        $related = new $relatedClass();
        return $related->where($foreignKey, $localValue)->get();
    }

    protected function hasOne(string $relatedClass, string $foreignKey, ?string $localKey = null): ?self
    {
        $localKey ??= $this->primaryKey;
        $localValue = $this->attributes[$localKey] ?? null;
        if ($localValue === null) {
            return null;
        }
        /** @var BaseModel $related */
        $related = new $relatedClass();
        return $related->where($foreignKey, $localValue)->first();
    }

    protected function belongsTo(string $relatedClass, string $foreignKey, ?string $ownerKey = null): ?self
    {
        $foreignValue = $this->attributes[$foreignKey] ?? null;
        if ($foreignValue === null) {
            return null;
        }
        if ($ownerKey) {
            return (new $relatedClass())->where($ownerKey, $foreignValue)->first();
        }
        return $relatedClass::find($foreignValue);
    }

    /**
     * Many-to-many via pivot table
     */
    protected function belongsToMany(
        string $relatedClass,
        string $pivotTable,
        string $foreignPivotKey,
        string $relatedPivotKey
    ): array {
        $localValue = $this->attributes[$this->primaryKey] ?? null;
        if ($localValue === null) {
            return [];
        }
        /** @var BaseModel $related */
        $related = new $relatedClass();
        $sql = "SELECT r.* FROM `{$related->getTable()}` r "
             . "INNER JOIN `{$pivotTable}` p ON p.`{$relatedPivotKey}` = r.`{$related->getPrimaryKey()}` "
             . "WHERE p.`{$foreignPivotKey}` = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$localValue]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $model = new $relatedClass($row);
            $model->exists = true;
            $results[] = $model;
        }
        return $results;
    }

    // ─── Transactions ─────────────────────────────────────────────

    public static function beginTransaction(): void
    {
        db()->beginTransaction();
    }

    public static function commit(): void
    {
        db()->commit();
    }

    public static function rollback(): void
    {
        db()->rollBack();
    }

    public static function transaction(callable $callback): mixed
    {
        $db = db();
        $db->beginTransaction();
        try {
            $result = $callback($db);
            $db->commit();
            return $result;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // ─── Raw query helpers ────────────────────────────────────────

    public static function raw(string $sql, array $bindings = []): array
    {
        $db = db();
        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public static function rawFirst(string $sql, array $bindings = []): ?array
    {
        $db = db();
        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Full-text search helper (MySQL MATCH ... AGAINST)
     */
    public function fullTextSearch(string $columns, string $term): self
    {
        $this->wheres[] = "MATCH({$columns}) AGAINST(? IN BOOLEAN MODE)";
        $this->bindings[] = $term;
        return $this;
    }
}
