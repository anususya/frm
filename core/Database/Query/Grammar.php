<?php

declare(strict_types=1);

namespace Core\Database\Query;

use Core\Database\Grammar as BaseGrammar;
use Core\Database\Noname\Collection;

class Grammar extends BaseGrammar
{
    /**
     * @var array<string>
     */
    protected array $selectComponents = [
        'columns',
        'from',
        'wheres',
        'orders',
        'limit'
    ];

    public function compileSelect(Builder $query): string
    {
        $original = $query->columns;

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        $sql = trim($this->concatenate(
            $this->compileComponents($query)
        ));

        $query->columns = $original;

        return $sql;
    }

    /**
     * @param array<mixed> $segments
     *
     * @return string
     */
    protected function concatenate(array $segments): string
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string) $value !== '';
        }));
    }

    /**
     * @param Builder $query
     *
     * @return array<string, mixed>
     */
    protected function compileComponents(Builder $query): array
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

    /**
     * @param Builder $query
     * @param array<mixed> $columns
     *
     * @return string
     */
    protected function compileColumns(Builder $query, array $columns): string
    {
        return 'select ' . $this->columnize($columns);
    }

    protected function compileFrom(Builder $query, string $table): string
    {
        return 'from ' . $table;
    }

    /**
     * @param array<mixed> $columns
     *
     * @return string
     */
    public function columnize(array $columns): string
    {
        return implode(', ', array_map($this->wrap(...), $columns));
    }

    public function compileWheres(Builder $query): string
    {
        if (count($sql = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWhereClauses($sql);
        }

        return '';
    }

    /**
     * @param Builder $query
     *
     * @return array<mixed>
     */
    protected function compileWheresToArray(Builder $query): array
    {
        return (new Collection($query->wheres))
            ->map(fn ($where) => $where['boolean'] . ' ' . $this->{"where{$where['type']}"}($query, $where))
            ->all();
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereNested(Builder $query, array $where): string
    {
        $offset = 6;

        return '(' . substr($this->compileWheres($where['query']), $offset) . ')';
    }

    /**
     * @param array<mixed> $sql
     *
     * @return string
     */
    protected function concatenateWhereClauses(array $sql): string
    {
        return 'where ' . $this->removeLeadingBoolean(implode(' ', $sql));
    }

    protected function removeLeadingBoolean(string $value): ?string
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }

    /**
     * @param Builder $query
     * @param array<mixed>   $where
     *
     * @return string
     */
    protected function whereBasic(Builder $query, array $where): string
    {
        $value = $this->parameter($where['value']);

        $operator = str_replace('?', '??', $where['operator']);

        return $this->wrap($where['column']) . ' ' . $operator . ' ' . $value;
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereIn(Builder $query, array $where): string
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . $this->parameterize($where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereNotIn(Builder $query, array $where): string
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . $this->parameterize($where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereNotInRaw(Builder $query, array $where): string
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . implode(
                ', ',
                $where['values']
            ) . ')';
        }

        return '1 = 1';
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereInRaw(Builder $query, array $where): string
    {
        if (! empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . implode(', ', $where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereNull(Builder $query, array $where): string
    {
        return $this->wrap($where['column']) . ' is null';
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereNotNull(Builder $query, array $where): string
    {
        return $this->wrap($where['column']) . ' is not null';
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereBetween(Builder $query, array $where): string
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->parameter(is_array($where['values']) ? reset($where['values']) : $where['values'][0]);

        $max = $this->parameter(is_array($where['values']) ? end($where['values']) : $where['values'][1]);

        return $this->wrap($where['column']) . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * @param Builder $query
     * @param array<mixed> $where
     *
     * @return string
     */
    protected function whereRowValues(Builder $query, array $where): string
    {
        $columns = $this->columnize($where['columns']);

        $values = $this->parameterize($where['values']);

        return '(' . $columns . ') ' . $where['operator'] . ' (' . $values . ')';
    }

    /**
     * @param Builder $query
     * @param array<mixed> $orders
     *
     * @return string
     */
    protected function compileOrders(Builder $query, array $orders): string
    {
        if (! empty($orders)) {
            return 'order by ' . implode(', ', $this->compileOrdersToArray($query, $orders));
        }

        return '';
    }

    /**
     * @param Builder $query
     * @param array<mixed> $orders
     *
     * @return array<string>
     */
    protected function compileOrdersToArray(Builder $query, array $orders): array
    {
        return array_map(function ($order) {
            return $this->wrap($order['column']) . ' ' . $order['direction'];
        }, $orders);
    }

    /**
     * @param array<mixed> $segments
     *
     * @return string
     */
    protected function wrapSegments(array $segments): string
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
    protected function wrapValue(string $value): string
    {
        if ($value !== '*') {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    public function compileDelete(Builder $query): string
    {
        $table = $query->from;

        $where = $this->compileWheres($query);

        return trim($this->compileDeleteWithoutJoins($query, $table, $where));
    }

    protected function compileDeleteWithoutJoins(Builder $query, string $table, string $where): string
    {
        return "delete from $table $where";
    }

    /**
     * @param Builder $query
     * @param array<string, mixed>   $values
     * @param null|string $sequence
     *
     * @return string
     */
    public function compileInsertGetId(Builder $query, array $values, ?string $sequence): string
    {
        return $this->compileInsert($query, $values) . ' returning ' . $this->wrap($sequence ?: 'id');
    }

    /**
     * @param Builder $query
     * @param array<mixed> $values
     *
     * @return string
     */
    public function compileInsert(Builder $query, array $values): string
    {
        $table = $query->from;

        if (empty($values)) {
            return "insert into $table default values";
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

    /**
     * @param Builder $query
     * @param array<string> $values
     *
     * @return string
     */
    public function compileUpdate(Builder $query, array $values): string
    {
        $table = $query->from;

        $columns = $this->compileUpdateColumns($query, $values);

        $where = $this->compileWheres($query);

        return trim($this->compileUpdateWithoutJoins($query, $table, $columns, $where));
    }

    protected function compileUpdateWithoutJoins(Builder $query, string $table, string $columns, string $where): string
    {
        return "update $table set $columns $where";
    }

    /**
     * @param Builder $query
     * @param array<mixed> $values
     *
     * @return string
     */
    protected function compileUpdateColumns(Builder $query, array $values): string
    {
        return (new Collection($values))->map(function ($value, $key) {
            return $this->wrap($key) . ' = ' . $this->parameter($value);
        })->implode(', ');
    }

    protected function compileLimit(Builder $query, string|int $limit): string
    {
        return 'limit ' . (int) $limit;
    }
}
