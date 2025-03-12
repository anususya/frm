<?php

namespace Core\Database\Query\Grammars;

use Core\Database\Grammar as BaseGrammar;
use Core\Database\Query\Builder;
use Core\Database\Collection;

class Grammar extends BaseGrammar
{
    protected $selectComponents = [
        'columns',
        'from',
        'wheres',
        'orders',
        'limit'
    ];

    public function compileSelect(Builder $query)
    {
        //$table = $query->from;
       // $sql = 'SELECT * FROM ' . $table;
        //return $sql;

        // If the query does not have any columns set, we'll set the columns to the
        // * character to just get all of the columns from the database. Then we
        // can build the query and concatenate all the pieces together as one.
        $original = $query->columns ?? $query->columns = ['*'];

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        // To compile the query, we'll spin through each component of the query and
        // see if that component exists. If it does we'll just call the compiler
        // function for the component which is responsible for making the SQL.
        $sql = trim($this->concatenate(
            $this->compileComponents($query)
        ));

        $query->columns = $original;

        return $sql;
    }

    protected function concatenate($segments)
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string) $value !== '';
        }));
    }

    protected function compileComponents(Builder $query)
    {
        $sql = [];

        foreach ($this->selectComponents as $component) {
            if (isset($query->$component)) {
                $method = 'compile' . ucfirst($component);

                $sql[$component] = $this->$method($query, $query->$component);
            }
        }

        return $sql;
    }
    protected function compileColumns(Builder $query, $columns)
    {
        return 'select ' . $this->columnize($columns);
    }

    protected function compileFrom(Builder $query, $table)
    {
        return 'from ' . $table;
    }

    public function columnize(array $columns)
    {
        return implode(', ', $columns);
        //return implode(', ', array_map($this->wrap(...), $columns));
    }

    public function compileWheres(Builder $query)
    {
        if (is_null($query->wheres)) {
            return '';
        }

        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience so we can
        // avoid checking for the first clauses in each of the compilers methods.
        if (count($sql = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWhereClauses($query, $sql);
        }

        return '';
    }

    protected function compileWheresToArray($query)
    {
        return (new Collection($query->wheres))
            ->map(fn ($where) => $where['boolean'] . ' ' . $this->{"where{$where['type']}"}($query, $where))
            ->all();
    }
    protected function whereNested(Builder $query, $where)
    {
        // Here we will calculate what portion of the string we need to remove. If this
        // is a join clause query, we need to remove the "on" portion of the SQL and
        // if it is a normal query we need to take the leading "where" of queries.
        $offset = 6;

        return '('.substr($this->compileWheres($where['query']), $offset).')';
    }
    protected function concatenateWhereClauses($query, $sql)
    {
        return 'where ' . $this->removeLeadingBoolean(implode(' ', $sql));
    }

    protected function removeLeadingBoolean($value)
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }

    protected function whereBasic(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        $operator = str_replace('?', '??', $where['operator']);

        return $this->wrap($where['column']) . ' ' . $operator . ' ' . $value;
    }

    protected function whereIn(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . $this->parameterize($where['values']) . ')';
        }

        return '0 = 1';
    }

    protected function whereNotIn(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . $this->parameterize($where['values']) . ')';
        }

        return '1 = 1';
    }

    protected function whereNotInRaw(Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . implode(
                ', ',
                $where['values']
            ) . ')';
        }

        return '1 = 1';
    }

    protected function whereInRaw(Builder $query, $where)
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . implode(', ', $where['values']) . ')';
        }

        return '0 = 1';
    }


    protected function whereNull(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' is null';
    }
    protected function whereNotNull(Builder $query, $where)
    {
        return $this->wrap($where['column']) . ' is not null';
    }

    protected function whereBetween(Builder $query, $where)
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->parameter(is_array($where['values']) ? reset($where['values']) : $where['values'][0]);

        $max = $this->parameter(is_array($where['values']) ? end($where['values']) : $where['values'][1]);

        return $this->wrap($where['column']) . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    protected function whereRowValues(Builder $query, $where)
    {
        $columns = $this->columnize($where['columns']);

        $values = $this->parameterize($where['values']);

        return '(' . $columns . ') ' . $where['operator'] . ' (' . $values . ')';
    }

    protected function compileOrders(Builder $query, $orders)
    {
        if (! empty($orders)) {
            return 'order by ' . implode(', ', $this->compileOrdersToArray($query, $orders));
        }

        return '';
    }
    public function wrap($value)
    {

        if (stripos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value);
        }

        return $this->wrapSegments(explode('.', $value));
    }

    /**
     * Wrap a value that has an alias.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapAliasedValue($value)
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        return $this->wrap($segments[0]) . ' as ' . $this->wrapValue($segments[1]);
    }

    /**
     * Wrap the given value segments.
     *
     * @param  array  $segments
     * @return string
     */
    protected function wrapSegments($segments)
    {
        return (new Collection($segments))->map(function ($segment, $key) use ($segments) {
            return $key == 0 && count($segments) > 1
                ? $this->wrapTable($segment)
                : $this->wrapValue($segment);
        })->implode('.');
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value !== '*') {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    public function compileDelete(Builder $query)
    {
        $table = $query->from;

        $where = $this->compileWheres($query);

        return trim($this->compileDeleteWithoutJoins($query, $table, $where));
    }

    protected function compileDeleteWithoutJoins(Builder $query, $table, $where)
    {
        return "delete from {$table} {$where}";
    }

    public function compileInsertGetId(Builder $query, $values, $sequence)
    {
        return $this->compileInsert($query, $values) . ' returning ' . $this->wrap($sequence ?: 'id');
    }

    public function compileInsert(Builder $query, array $values)
    {
        $table = $query->from;

        if (empty($values)) {
            return "insert into {$table} default values";
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        // We need to build a list of parameter place-holders of values that are bound
        // to the query. Each insert should have the exact same number of parameter
        // bindings so we will loop through the record and parameterize them all.
        $parameters = (new Collection($values))->map(function ($record) {
            return '(' . $this->parameterize($record) . ')';
        })->implode(', ');

        return "insert into $table ($columns) values $parameters";
    }


    public function compileUpdate(Builder $query, array $values)
    {
        $table = $query->from;

        $columns = $this->compileUpdateColumns($query, $values);

        $where = $this->compileWheres($query);

        return trim($this->compileUpdateWithoutJoins($query, $table, $columns, $where));
    }

    protected function compileUpdateWithoutJoins(Builder $query, $table, $columns, $where)
    {
        return "update {$table} set {$columns} {$where}";
    }

    protected function compileUpdateColumns(Builder $query, array $values)
    {
        return (new Collection($values))->map(function ($value, $key) {
            return $this->wrap($key) . ' = ' . $this->parameter($value);
        })->implode(', ');
    }

    protected function compileLimit(Builder $query, $limit)
    {
        return 'limit '.(int) $limit;
    }

    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $cleanBindings = array_diff($bindings, ['select', 'join']);

        $values = self::flatten(array_map(fn ($value) => value($value), $values));

        return array_values(
            array_merge($bindings['join'], $values, Arr::flatten($cleanBindings))
        );
    }
}
