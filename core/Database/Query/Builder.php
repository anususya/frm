<?php

namespace Core\Database\Query;

use Core\Database\Connection;
use Core\Database\ConnectionInteface as ConnectionInterface;
use Core\Database\Noname\Builder as NonameBuilder;
use Core\Database\Collection;
use Core\Database\Query\Grammars\Grammar;
use InvalidArgumentException;

class Builder
{
    /**
     * @var null|array<mixed>
     */
    public ?array $columns;

    public string $from;

    public ?array $wheres = [];
    public ?int $limit;

    public Grammar $grammar;

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
        public ConnectionInterface $connection
    ) {
        $this->grammar = $connection->getQueryGrammar();
    }

    public function select($columns = ['*']): Builder
    {
        $this->columns = [];
        $this->bindings['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }


    public function from($table, $as = null)
    {
        $this->from = $table;

        return $this;
    }


    public function addSelect($column)
    {
        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $column) {
            if (is_array($this->columns) && in_array($column, $this->columns, true)) {
                continue;
            }

            $this->columns[] = $column;
        }

        return $this;
    }

    public function limit($value)
    {
        if ($value >= 0) {
            $this->limit = ! is_null($value) ? (int) $value : null;
        }

        return $this;
    }

    public function get($columns = ['*'])
    {
        return new Collection($this->runSelect());
    }

    protected function runSelect()
    {
        return $this->connection->select($this->toSql(), $this->getBindings());
    }

    public function toSql()
    {
        return $this->grammar->compileSelect($this);
    }

    public function getBindings()
    {
        return self::flatten($this->bindings);
    }

    public static function flatten(array $array, $depth = INF)
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
    public function getConnection()
    {
        return $this->connection;
    }

    public function delete($id = null)
    {
        // If an ID is passed to the method, we will set the where clause to check the
        // ID to let developers to simply and quickly remove a single row from this
        // database without manually specifying the "where" clauses on the query.
        if (! is_null($id)) {
            $this->where($this->from.'.id', '=', $id);
        }

        return $this->connection->delete($this->grammar->compileDelete($this), self::flatten($this->bindings));
        return $this->connection->delete(
            $this->grammar->compileDelete($this),
            $this->cleanBindings(
                $this->grammar->prepareBindingsForDelete($this->bindings)
            )
        );
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );



        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        $type = 'Basic';

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        //$this->addBinding($this->flattenValue($value), 'where');
        $this->addBinding($value, 'where');
        return $this;
    }

    protected function invalidOperator($operator)
    {
        return ! is_string($operator) || (! in_array(strtolower($operator), $this->operators, true));
    }

    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     *
     * @param  string  $operator
     * @param  mixed  $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
            ! in_array($operator, ['=', '<>', '!=']);
    }

    protected function addArrayOfWheres($column, $boolean, $method = 'where')
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

    public function whereNested(\Closure $callback, $boolean = 'and')
    {
        $callback($query = $this->forNestedWhere());

        return $this->addNestedWhereQuery($query, $boolean);
    }

    public function newQuery()
    {
        return new static($this->connection);
    }
    public function forNestedWhere()
    {
        return $this->newQuery()->from($this->from);
    }

    public function addNestedWhereQuery($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getRawBindings()['where'], 'where');
        }

        return $this;
    }
    public function getRawBindings()
    {
        return $this->bindings;
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';
        $this->wheres[] = compact('type', 'column', 'values', 'boolean');
        $this->addBinding($values, 'where');

        return $this;
    }

    public function whereRowValues($columns, $operator, $values, $boolean = 'and')
    {
        if (count($columns) !== count($values)) {
            throw new InvalidArgumentException('The number of columns must match the number of values');
        }

        $type = 'RowValues';

        $this->wheres[] = compact('type', 'columns', 'operator', 'values', 'boolean');

        //$this->addBinding($this->cleanBindings($values));

        return $this;
    }

    public function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotInRaw' : 'InRaw';

        $values = self::flatten($values);

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        return $this;
    }


    public function addBinding($value, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values($value);
        } else {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }

    public function insertGetId(array $values, $sequence = null)
    {
        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

        return $this->processInsertGetId($this, $sql, $values, $sequence);
    }

    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $id = $query->getConnection()->insert($sql, $values);

       //id = $query->getConnection()->getPdo()->fetchColumn();

        return is_numeric($id) ? (int) $id : $id;
    }

    public function processInsertGetId1(Builder $query, $sql, $values, $sequence = null)
    {
        $connection = $query->getConnection();

        $connection->recordsHaveBeenModified();

        $result = $connection->select($sql, array_values($values))[0];

        $sequence = $sequence ?: 'id';

        $id = is_object($result) ? $result->{$sequence} : $result[$sequence];

        return is_numeric($id) ? (int) $id : $id;
    }

    public function update(array $values)
    {
        $sql = $this->grammar->compileUpdate($this, $values);
        return $this->connection->update($sql, array_values(array_merge($values, $this->getBindings())));
    }



}
