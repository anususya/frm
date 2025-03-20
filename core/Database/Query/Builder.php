<?php

declare(strict_types=1);

namespace Core\Database\Query;

use Closure;
use Core\Database\Connection;
use Core\Database\Noname\Collection;
use InvalidArgumentException;

class Builder
{
    /**
     * @var null|array<mixed>
     */
    public ?array $columns;

    public string $from;

    /**
     * @var array<int|string, mixed>
     */
    public array $wheres = [];

    /**
     * @var array<mixed>
     */
    public array $orders;
    public ?int $limit;

    /**
     * @var array<mixed>
     */
    public array $bindings = [
        'select' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'order' => [],
        'union' => [],
        'unionOrder' => [],
    ];

    /**
     * @var array<string>
     */
    public array $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>', '&~', 'is', 'is not',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    public function __construct(
        public Connection $connection,
        public Grammar $grammar
    ) {
    }

    public function select(mixed $columns = ['*']): Builder
    {
        $this->columns = [];
        $this->bindings['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    public function from(string $table): Builder
    {
        $this->from = $table;

        return $this;
    }

    public function addSelect(mixed $column): Builder
    {
        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $column) {
            if (isset($this->columns) && in_array($column, $this->columns, true)) {
                continue;
            }

            $this->columns[] = $column;
        }

        return $this;
    }

    public function limit(null|string|int $value): Builder
    {
        if ($value >= 0) {
            $this->limit = ! is_null($value) ? (int) $value : null;
        }

        return $this;
    }

    public function get(mixed $columns = ['*']): Collection
    {
        return new Collection($this->onceWithColumns(self::wrap($columns), function () {
            return $this->runSelect();
        }));
    }

    /**
     * @param mixed $value
     *
     * @return array<mixed>
     */
    public static function wrap(mixed $value): array
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * @param string[] $columns
     * @param callable $callback
     *
     * @return mixed
     */
    protected function onceWithColumns(array $columns, callable $callback): mixed
    {
        $original = $this->columns ?? null;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        $result = $callback();

        $this->columns = $original;

        return $result;
    }

    /**
     * @return mixed[]
     */
    protected function runSelect(): array
    {
        return $this->connection->select($this->toSql(), $this->getBindings());
    }

    public function toSql(): string
    {
        return $this->grammar->compileSelect($this);
    }

    /**
     * @return mixed[]
     */
    public function getBindings(): array
    {
        return self::flatten($this->bindings);
    }

    /**
     * @param array<mixed> $array
     * @param int $depth
     *
     * @return array<mixed>
     */
    public static function flatten(array $array, int $depth = 1): array
    {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (! is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : self::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function delete(int|string $id = null): int
    {
        if (! is_null($id)) {
            $this->where($this->from . '.id', '=', $id);
        }

        return $this->connection->delete($this->grammar->compileDelete($this), self::flatten($this->bindings));
    }

    public function where(
        mixed $column,
        string $operator = '=',
        mixed $value = null,
        string $boolean = 'and'
    ): Builder {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        $type = 'Basic';

        $this->wheres[] = compact(
            'type',
            'column',
            'operator',
            'value',
            'boolean'
        );

        $this->addBinding($value);

        return $this;
    }

    public function whereNull(mixed $columns, string $boolean = 'and', bool $not = false): Builder
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (self::wrap($columns) as $column) {
            $this->wheres[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    protected function invalidOperator(string $operator): bool
    {
        return ! in_array(strtolower($operator), $this->operators, true);
    }

    /**
     * @param mixed  $value
     * @param string $operator
     * @param bool   $useDefault
     *
     * @return array<mixed>
     */
    public function prepareValueAndOperator(mixed $value, string $operator, bool $useDefault = false): array
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    protected function invalidOperatorAndValue(string $operator, mixed $value): bool
    {
        return is_null($value) && in_array($operator, $this->operators) &&
            ! in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * @param mixed $column
     * @param string $boolean
     * @param string $method
     *
     * @return Builder
     */
    protected function addArrayOfWheres(mixed $column, string $boolean, string $method = 'where'): Builder
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value), boolean: $boolean);
                } else {
                    $query->{$method}($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }

    public function whereNested(Closure $callback, string $boolean = 'and'): Builder
    {
        $callback($query = $this->forNestedWhere());

        return $this->addNestedWhereQuery($query, $boolean);
    }

    public function newQuery(): static
    {
        return new static($this->connection, $this->grammar); // @phpstan-ignore-line
    }
    public function forNestedWhere(): Builder
    {
        return $this->newQuery()->from($this->from);
    }

    public function addNestedWhereQuery(Builder $query, string $boolean = 'and'): Builder
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getRawBindings()['where']);
        }

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getRawBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param string $column
     * @param array<string>|string $values
     * @param string       $boolean
     * @param bool         $not
     *
     * @return $this
     */
    public function whereIn(
        string $column,
        array|string $values,
        string $boolean = 'and',
        bool $not = false
    ): Builder {
        $type = $not ? 'NotIn' : 'In';
        $this->wheres[] = compact('type', 'column', 'values', 'boolean');
        $this->addBinding($values);

        return $this;
    }

    /**
     * @param mixed $column
     * @param array<int>  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return $this
     */
    public function whereIntegerInRaw(
        mixed $column,
        array $values,
        string $boolean = 'and',
        bool $not = false
    ): Builder {
        $type = $not ? 'NotInRaw' : 'InRaw';

        $values = self::flatten($values);

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        return $this;
    }

    public function addBinding(mixed $value, string $type = 'where'): Builder
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: $type.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values($value);
        } else {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }

    /**
     * @param array<mixed> $values
     *
     * @return bool
     */
    public function insert(array $values): bool
    {
        if (empty($values)) {
            return true;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        return $this->connection->insert(
            $this->grammar->compileInsert($this, $values),
            self::flatten($values)
        );
    }

    /**
     * @param array<string, mixed> $values
     * @param string|null $sequence
     *
     * @return int|string
     */
    public function insertGetId(array $values, ?string $sequence = null): string|int
    {
        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

        return $this->processInsertGetId($this, $sql, $values, $sequence);
    }

    /**
     * @param Builder $query
     * @param string  $sql
     * @param array<string, mixed>   $values
     * @param null|string  $sequence
     *
     * @return string|int
     */
    public function processInsertGetId(Builder $query, string $sql, array $values, ?string $sequence): string|int
    {
        $connection = $query->getConnection();

        $connection->recordsHaveBeenModified();

        $result = $connection->select($sql, $values)[0];

        $sequence = $sequence ?: 'id';

        $id = is_object($result) ? $result->{$sequence} : $result[$sequence];

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return int
     */
    public function update(array $values): int
    {
        $sql = $this->grammar->compileUpdate($this, $values);
        return $this->connection->update($sql, array_values(array_merge($values, $this->getBindings())));
    }

    public function orderBy(string $column, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction);

        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }
}
