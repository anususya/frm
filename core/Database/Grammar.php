<?php

namespace Core\Database;

use Core\Database\Noname\Collection;
use Core\Database\Query\Expression;

abstract class Grammar
{
    public function __construct(
        protected ConnectionInterface $connection
    ) {
    }

    public function wrapTable(string $table, ?string $prefix = null): string
    {
        return $this->wrapValue($prefix . $table);
    }

    public function wrap(string|Expression $value): string
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        return $this->wrapSegments(explode('.', $value)); //@phpstan-ignore-line
    }

    public function isExpression(mixed $value): bool
    {
        return $value instanceof Expression;
    }

    public function getValue(mixed $expression): mixed
    {
        if ($this->isExpression($expression)) {
            return $this->getValue($expression->getValue($this));
        }

        return $expression;
    }

    /**
     * Wrap the given value segments.
     *
     * @param  array<mixed>  $segments
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

    protected function wrapValue(string $value): string
    {
        if ($value !== '*') {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    /**
     * Convert an array of column names into a delimited string.
     *
     * @param  array<mixed>  $columns
     * @return string
     */
    public function columnize(array $columns): string
    {
        return implode(', ', array_map($this->wrap(...), $columns));
    }

    /**
     * Create query parameter place-holders for an array.
     *
     * @param  array<mixed>  $values
     * @return string
     */
    public function parameterize(array $values): string
    {
        return implode(', ', array_map($this->parameter(...), $values));
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     *
     * @param  mixed  $value
     * @return string
     */
    public function parameter(mixed $value): string
    {
        return '?';
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    }
}
