<?php

declare(strict_types=1);

namespace Core\Database\Schema;

use Core\Database\Connection;

class Blueprint
{
    /**
     * @var array<int, ColumnDefinition>
     */
    protected array $columns = [];
    protected string $command;
    public function __construct(
        protected Connection $connection,
        protected string $table,
        protected Grammar $grammar
    ) {
    }

    /**
     * @param string $name
     * @param string $type
     * @param array<mixed> $parameters
     *
     * @return ColumnDefinition
     */
    public function addColumn(string $name, string $type, array $parameters = []): ColumnDefinition
    {
        return $this->addColumnDefinition(new ColumnDefinition(
            array_merge(compact('type', 'name'), $parameters)
        ));
    }
    protected function addColumnDefinition(ColumnDefinition $definition): ColumnDefinition
    {
        $this->columns[] = $definition;

        return $definition;
    }
    public function increments(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'serial');
    }
    public function integer(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'int4');
    }
    public function boolean(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'boolean');
    }
    public function string(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'varchar');
    }
    public function float(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'float');
    }
    public function date(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'date');
    }

    public function build(): void
    {
        foreach ($this->toSql() as $statement) {
            $this->connection->statement($statement);
        }
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return array<int, ColumnDefinition>
     */
    public function getAddedColumns(): array
    {
        return array_filter($this->columns, function ($column) {
            return ! isset($column->change);
        });
    }

    /**
     * @return array<mixed>
     */
    public function toSql(): array
    {
        $method = 'compile' . ucfirst($this->command);
        $statements = [];
        if (method_exists($this->grammar, $method)) {
            if (! is_null($sql = $this->grammar->$method($this))) {
                $statements = array_merge($statements, (array) $sql);
            }
        }

        return $statements;
    }

    public function create(): Blueprint
    {
        return $this->addCommand('create');
    }

    protected function addCommand(string $name): Blueprint
    {
        $this->command = $name;

        return $this;
    }
}
