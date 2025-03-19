<?php

declare(strict_types=1);

namespace Core\Database\Schema;

use Core\Database\Connection;

class Builder
{
    protected Connection $connection;
    protected Grammar $grammar;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }

    public function create(string $table, callable $callback): void
    {
        $query = new Blueprint($this->connection, $table, $this->grammar);
        $query->create();
        $callback($query);
        $query->build();
    }
}
