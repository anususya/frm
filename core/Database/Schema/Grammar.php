<?php

declare(strict_types=1);

namespace Core\Database\Schema;

use Core\Database\Grammar as BaseGrammar;

class Grammar extends BaseGrammar
{
    /**
     * @var string[]
     */
    protected array $modifiers = ['Nullable', 'Default'];
    public function compileCreate(Blueprint $blueprint): string
    {
        return sprintf(
            '%s table %s (%s)',
            'create',
            $blueprint->getTable(),
            implode(', ', $this->getColumns($blueprint))
        );
    }

    /**
     * @param Blueprint $blueprint
     *
     * @return array<int, string>
     */
    protected function getColumns(Blueprint $blueprint): array
    {
        $columns = [];

        foreach ($blueprint->getAddedColumns() as $column) {
            $columns[] = $this->getColumn($blueprint, $column);
        }

        return $columns;
    }

    protected function getColumn(Blueprint $blueprint, ColumnDefinition $column): string
    {

        $sql = $column->name . ' ' . $column->type;

        return $this->addModifiers($sql, $blueprint, $column);
    }

    protected function addModifiers(string $sql, Blueprint $blueprint, ColumnDefinition $column): string
    {
        foreach ($this->modifiers as $modifier) {
            if (method_exists($this, $method = "modify$modifier")) {
                $sql .= $this->{$method}($blueprint, $column);
            }
        }

        return $sql;
    }

    protected function modifyNullable(Blueprint $blueprint, ColumnDefinition $column): string
    {
        if ($column->change) {
            return $column->nullable ? 'drop not null' : 'set not null';
        }

        return $column->nullable ? ' null' : ' not null';
    }

    protected function modifyDefault(Blueprint $blueprint, ColumnDefinition $column): string
    {
        if (! is_null($column->default)) {
            $value = is_bool($column->default)
                ? "'" . (int) $column->default . "'"
                : "'" . $column->default . "'";
            return ' default ' . $value;
        }

        return '';
    }
}
