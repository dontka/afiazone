<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\BaseModel;

/**
 * Base Repository — abstracts data access from services.
 */
abstract class BaseRepository
{
    protected BaseModel $model;

    public function __construct(BaseModel $model)
    {
        $this->model = $model;
    }

    public function find(mixed $id): ?BaseModel
    {
        return $this->model::find($id);
    }

    public function findOrFail(mixed $id): BaseModel
    {
        return $this->model::findOrFail($id);
    }

    public function findBy(string $column, mixed $value): ?BaseModel
    {
        return $this->model::findBy($column, $value);
    }

    public function findAllBy(string $column, mixed $value): array
    {
        return $this->model::findAllBy($column, $value);
    }

    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        return $this->model::all($orderBy, $direction);
    }

    public function create(array $attributes): BaseModel
    {
        return $this->model::create($attributes);
    }

    public function update(mixed $id, array $attributes): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->update($attributes);
        }
        return false;
    }

    public function delete(mixed $id): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->delete();
        }
        return false;
    }

    public function count(): int
    {
        return $this->model::query()->count();
    }

    public function exists(string $column, mixed $value): bool
    {
        return $this->model::query()->where($column, $value)->exists();
    }

    public function paginate(int $page = 1, int $perPage = 20, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        return $this->model::query()
            ->orderBy($orderBy, $direction)
            ->paginate($page, $perPage);
    }

    /**
     * Wrap a callback in a database transaction
     */
    public function transaction(callable $callback): mixed
    {
        return BaseModel::transaction($callback);
    }

    /**
     * Return a fresh query builder on the model
     */
    public function query(): BaseModel
    {
        return $this->model::query();
    }
}
