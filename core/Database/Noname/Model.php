<?php

declare(strict_types=1);

namespace Core\Database\Noname;

use Core\Database\ConnectionInterface;
use Core\Database\ConnectionResolverInterface as Resolver;
use Core\Database\Query\Builder as QueryBuilder;

/**
 * @mixin Builder
 */
abstract class Model
{
    protected static string $builder = Builder::class;
    protected bool $exists = false;
    protected string $primaryKey = 'id';
    protected string $table;
    protected ?string $connection;
    protected bool $incrementing = true;
    protected string $keyType = 'int';

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<string, mixed>
     */
    protected array $original = [];
    protected static Resolver $resolver;

    /**
     * @param array<string, mixed> $attributes
     *
     * @return bool
     */
    public function update(array $attributes = []): bool
    {
        if (!$this->exists) {
            return false;
        }

        return $this->fill($attributes)->save();
    }

    public function save(): bool
    {
        $query = $this->newModelQuery();

        if ($this->exists) {
            $dirty = $this->getDirty();
            $saved = !(count($dirty) > 0) || $this->performUpdate($query, $dirty);
        } else {
            $saved = $this->performInsert($query);

            if (!$this->getConnectionName()) {
                $connection = $query->getConnection();
                $this->setConnection($connection->getName());
            }
        }

        if ($saved) {
            $this->syncOriginal();
        }

        return $saved;
    }

    /**
     * @param Builder $query
     * @param array<string, mixed> $dirty
     *
     * @return bool
     */
    protected function performUpdate(Builder $query, array $dirty): bool
    {
        $this->setKeysForSaveQuery($query)->update($dirty);

        return true;
    }

    protected function performInsert(Builder $query): bool
    {

        if (empty($attributes = $this->getAttributesForInsert())) {
            return true;
        }

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        } else {

            /**
             * TODO insert if id not ai
             */
            $query->insert($attributes);
        }

        return true;
    }

    /**
     * @param Builder $query
     * @param array<string, mixed> $attributes
     *
     * @return void
     */
    protected function insertAndSetId(Builder $query, array $attributes): void
    {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

        $this->setAttribute($keyName, $id);
    }

    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAttributesForInsert(): array
    {
        return $this->getAttributes();
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    protected function setKeysForSaveQuery(Builder $query): Builder
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }

    protected function getKeyForSaveQuery(): mixed
    {
        return $this->original[$this->getKeyName()] ?? $this->getKey();
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getKey(): int|string|null
    {
        return $this->attributes[$this->getKeyName()];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDirty(): array
    {
        return array_filter($this->getAttributes(), function ($key) {
            return !$this->originalIsEquivalent($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function originalIsEquivalent(string $key): bool
    {
        if (!array_key_exists($key, $this->original)) {
            return false;
        }

        $attribute = $this->attributes[$key] ?? null;
        $original = $this->original[$key] ?? null;

        if ($attribute === $original) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return $this
     */
    public function fill(array $attributes): Model
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }
    public function setAttribute(string $key, mixed $value): Model
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param string|int|array<string|int> $ids
     *
     * @return int
     */
    public static function destroy(array|string|int $ids): int
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (count($ids) === 0) {
            return 0;
        }

        $instance = new static(); // @phpstan-ignore-line
        $query = $instance->newModelQuery();
        $count = 0;

        $query->whereIn($instance->getKeyName(), $ids);
        $models = $query->get();
        foreach ($models->all() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }
    public function delete(): bool
    {
        $this->performDeleteOnModel();

        return true;
    }

    protected function performDeleteOnModel(): void
    {
        $this->setKeysForSaveQuery($this->newModelQuery())->delete();

        $this->exists = false;
    }

    /**
     * @param string|string[] $columns
     *
     * @return Collection
     */
    public static function all(string|array $columns = ['*']): Collection
    {
        return static::query()->get(
            is_array($columns) ? $columns : func_get_args()
        );
    }

    public static function query(): Builder
    {
        return (new static())->newModelQuery(); // @phpstan-ignore-line
    }

    public function newModelQuery(): Builder
    {
        return $this->newNonameBuilder(
            $this->newBaseQueryBuilder()
        )->setModel($this);
    }

    public function newNonameBuilder(QueryBuilder $query): Builder
    {
        return new static::$builder($query); // @phpstan-ignore-line
    }
    protected function newBaseQueryBuilder(): QueryBuilder
    {
        return $this->getConnection()->query();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): Model
    {
        $this->table = $table;

        return $this;
    }

    public function getConnection(): ConnectionInterface
    {
        return static::resolveConnection($this->getConnectionName());
    }

    public static function setConnectionResolver(Resolver $resolver): void
    {
        static::$resolver = $resolver;
    }
    public static function resolveConnection(?string $connection): ConnectionInterface
    {
        return static::$resolver->connection($connection);
    }

    public function getConnectionName(): ?string
    {
        return $this->connection;
    }

    public function setConnection(?string $name): Model
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     * @param bool  $exists
     *
     * @return static
     */
    public function newInstance(array $attributes = [], bool $exists = false): static
    {
        // phpcs:ignore
        $model = new static(); // @phpstan-ignore-line

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        $model->fill($attributes);

        return $model;
    }

    /**
     * @param array<string, mixed> $attributes
     * @param string|null $connection
     *
     * @return static
     */
    public function newFromBuilder(array $attributes = [], string $connection = null): static
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes($attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        return $model;
    }

    /**
     * @param array<string, mixed> $attributes
     * @param bool $sync
     *
     * @return $this
     */
    public function setRawAttributes($attributes, bool $sync = false): Model
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    public function syncOriginal(): Model
    {
        $this->original = $this->getAttributes();

        return $this;
    }

    /**
     * @param array<mixed> $items
     *
     * @return Collection
     */
    public function newCollection(array $items): Collection
    {
        return new Collection($items);
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    protected function getAttribute(string $key): mixed
    {
        return $this->getAttributes()[$key] ?? null;
    }

    public static function __callStatic(string $method, array $parameters): mixed // @phpstan-ignore-line
    {
        // phpcs:ignore
        return (new static())->$method(...$parameters); // @phpstan-ignore-line
    }

    public function __call(string $method, array $parameters): mixed // @phpstan-ignore-line
    {
        return $this->forwardCallTo($this->newModelQuery(), $method, $parameters);
    }

    protected function forwardCallTo(Builder $object, string $method, array $parameters) // @phpstan-ignore-line
    {
        return $object->{$method}(...$parameters);
    }

    public function getKeyType(): string
    {
        return $this->keyType;
    }

    public function setKeyType(string $type): Model
    {
        $this->keyType = $type;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
