<?php

namespace Core\Database\Noname;

use Core\Database\Collection;
use Core\Database\ConnectionResolverInterface as Resolver;
use Core\Database\ConnectionInteface as Connection;
use Core\Database\Query\Builder as QueryBuilder;

class Model
{
    protected static string $builder = Builder::class;
    protected bool $exists = false;
    protected string $table;
    protected ?string $connection;
    protected bool $incrementing = true;

    protected $keyType = 'int';

    /**
     * @var array<mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<mixed>
     */
    protected array $original = [];
    protected static Resolver $resolver;

    public function create(): bool
    {
        return true;
    }

    /**
     * @param array<mixed> $attributes
     * @param array<mixed> $options
     *
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        if (!$this->exists) {
            return false;
        }

        return $this->fill($attributes)->save($options);
    }

    /**
     * @param array<mixed> $options
     *
     * @return bool
     */
    public function save(array $options = []): bool
    {
        $query = $this->newModelQuery();

        if ($this->exists) {
            $saved = $this->isDirty() ? $this->performUpdate($query) : true;
        } else {
            $saved = $this->performInsert($query);
            if (!$this->getConnectionName() && $connection = $query->getConnection()) {
                $this->setConnection($connection->getName());
            }
        }

        if ($saved) {
            $this->syncOriginal();
        }

        return $saved;
    }

    protected function performUpdate(Builder $query): bool
    {
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $this->setKeysForSaveQuery($query)->update($dirty);
        }

        return true;
    }

    protected function performInsert(Builder $query): bool
    {
        $attributes = $this->getAttributesForInsert();

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        } else {
            if (empty($attributes)) {
                return true;
            }

            $query->insert($attributes);
        }

        return true;
    }

    protected function insertAndSetId(Builder $query, $attributes): void
    {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

        $this->setAttribute($keyName, $id);
    }

    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }
    protected function getAttributesForInsert(): array
    {
        return $this->getAttributes();
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
    protected function setKeysForSaveQuery($query): Builder
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }

    protected function getKeyForSaveQuery(): mixed
    {
        return $this->original[$this->getKeyName()] ?? $this->getKey();
    }

    protected function setKeysForSelectQuery($query): Builder
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSelectQuery());

        return $query;
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    protected function getKeyForSelectQuery(): mixed
    {
        return $this->original[$this->getKeyName()] ?? $this->getKey();
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->getKeyName()];
    }


    public function isDirty($attributes = null): bool
    {
//        return $this->hasChanges(
//            $this->getDirty(), is_array($attributes) ? $attributes : func_get_args()
//        );
        return $this->getDirty() != null;
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

    public function fill(array $attributes): Model
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }
    public function setAttribute($key, $value): Model
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public static function forceDestroy($ids): int
    {
        return static::destroy($ids);
    }

    public static function destroy($ids): int
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (count($ids) === 0) {
            return 0;
        }

        // We will actually pull the models from the database table and call delete on
        // each of them individually so that their events get fired properly with a
        // correct set of attributes in case the developers wants to check these.
        $instance = new static();
        $key = $instance->getKeyName();

        $count = 0;
        $query = $instance->newModelQuery();
        $query->whereIn($key, $ids);
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
        return static::query()->delete();
    }

    protected function performDeleteOnModel(): void
    {
        $this->setKeysForSaveQuery($this->newModelQuery())->delete();

        $this->exists = false;
    }

    public static function all($columns = ['*']): Collection
    {
        return static::query()->get(
            is_array($columns) ? $columns : func_get_args()
        );
    }

    /**
     * Begin querying the model.
     */
    public static function query(): Builder
    {
        return (new static())->newModelQuery();
    }

    public function newModelQuery(): Builder
    {
        return $this->newNonameBuilder(
            $this->newBaseQueryBuilder()
        )->setModel($this);
    }

    public function newNonameBuilder(QueryBuilder $query): Builder
    {
        return new static::$builder($query);
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

    public function getConnection(): Connection
    {
        return static::resolveConnection($this->getConnectionName());
    }

    public static function setConnectionResolver(Resolver $resolver): void
    {
        static::$resolver = $resolver;
    }
    public static function resolveConnection(string $connection): Connection
    {
        return static::$resolver->connection($connection);
    }

    public function getConnectionName(): ?string
    {
        return $this->connection;
    }

    /**
     * Set the connection associated with the model.
     *
     * @param  string|null  $name
     * @return $this
     */
    public function setConnection(?string $name): Model
    {
        $this->connection = $name;

        return $this;
    }

    public function newInstance($attributes = [], $exists = false): Model
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static();

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        //$model->mergeCasts($this->casts);

        $model->fill((array) $attributes);

        return $model;
    }

    public function newFromBuilder($attributes = [], $connection = null): Model
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        return $model;
    }

    public function setRawAttributes(array $attributes, $sync = false): Model
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        //$this->classCastCache = [];
        //$this->attributeCastCache = [];

        return $this;
    }

    public function syncOriginal()
    {
        $this->original = $this->getAttributes();

        return $this;
    }

    /**
     * @param array<mixed> $items
     *
     * @return Collection
     */
    public function newCollection($items): Collection
    {
        return new Collection($items);
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    protected function getAttribute($key)
    {
        return $this->getAttributes()[$key] ?? null;
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->newModelQuery(), $method, $parameters);
    }

    protected function forwardCallTo($object, $method, $parameters)
    {
        return $object->{$method}(...$parameters);
    }

    public function getKeyType(): string
    {
        return $this->keyType;
    }

    public function setKeyType($type): Model
    {
        $this->keyType = $type;

        return $this;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
