<?php

declare(strict_types=1);

namespace Core\Database\Noname;

use Core\Database\Query\Builder as QueryBuilder;

/**
 * @mixin QueryBuilder
 */
class Builder
{
    protected Model $model;
    protected string $table;

    public function __construct(
        protected QueryBuilder $query
    ) {
    }

    public function setModel(Model $model): Builder
    {
        $this->model = $model;

        $this->query->from($model->getTable());

        return $this;
    }

    public function __call(string $method, $parameters): mixed // @phpstan-ignore-line
    {
        return $this->forwardCallTo($this->query, $method, $parameters);
    }

    protected function forwardCallTo(QueryBuilder $object, string $method, $parameters): mixed // @phpstan-ignore-line
    {
        return $object->{$method}(...$parameters);
    }

    /**
     * @param string[] $columns
     *
     * @return Collection
     */
    public function get(array $columns = ['*']): Collection
    {
        $models = $this->getModels($columns);

        return $this->getModel()->newCollection($models);
    }

    public function delete(): int
    {
        return $this->query->delete();
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return int
     */
    public function update(array $values): int
    {
        return $this->query->update($values);
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param string[] $columns
     *
     * @return mixed[]
     */
    public function getModels(array $columns = ['*']): array
    {
        return $this->hydrate($this->query->get($columns)->all())->all();
    }

    public function from(string $table): Builder
    {
        $this->query->from = $table;

        return $this;
    }

    /**
     * @param array<mixed> $items
     *
     * @return Collection
     */
    public function hydrate(array $items): Collection
    {
        $instance = $this->newModelInstance();

        return $instance->newCollection(
            array_map(function ($item) use ($instance) {
                $item = is_array($item) ? $item : (array) $item;
                return $instance->newFromBuilder($item);
            }, $items)
        );
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return Model
     */
    public function newModelInstance(array $attributes = []): Model
    {
        return $this->model->newInstance($attributes)->setConnection(
            $this->query->getConnection()->getName()
        );
    }

    /**
     * @param string|int $id
     * @param string[] $columns
     *
     * @return Model|null
     */
    public function find(string|int $id, array $columns = ['*']): ?Model
    {
        $this->limit(1);

        return $this->whereKey($id)->get($columns)->first();
    }

    /**
     * @param array<string|int> $ids
     * @param string[] $columns
     *
     * @return Collection
     */
    public function findMany(array $ids, array $columns = ['*']): Collection
    {
        if (empty($ids)) {
            return $this->model->newCollection([]);
        }

        return $this->whereKey($ids)->get($columns);
    }

    /**
     * @param string|int|null|array<string|int> $ids
     *
     * @return Builder
     */
    public function whereKey(string|int|null|array $ids): Builder
    {
        if (is_array($ids)) {
            if (in_array($this->model->getKeyType(), ['int', 'integer'])) {
                $this->query->whereIntegerInRaw($this->model->getKeyName(), $ids); // @phpstan-ignore-line
            } else {
                $this->query->whereIn($this->model->getKeyName(), $ids); // @phpstan-ignore-line
            }

            return $this;
        }

        if ($ids !== null && $this->model->getKeyType() === 'string') {
            $ids = (string) $ids;
        }

        return $this->where($this->model->getKeyName(), '=', $ids);
    }

    /**
     * @param string|array<mixed>    $column
     * @param string|null     $operator
     * @param int|string|null $value
     * @param string          $boolean
     *
     * @return $this
     */
    public function where(
        string|array $column,
        string $operator = null,
        null|int|string $value = null,
        string $boolean = 'and'
    ): Builder {
        $this->query->where(...func_get_args());

        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return Model
     */
    public function create(array $attributes = []): Model
    {
        $newModel = $this->newModelInstance($attributes);
        $newModel->save();

        return $newModel;
    }
}
