<?php

declare(strict_types=1);

namespace Core\Database;

use Closure;
use Core\Database\Query\Builder as QueryBuilder;
use Core\Database\Query\Grammar as QueryGrammar;
use Core\Database\Schema\Builder as SchemaBuilder;
use Core\Database\Schema\Grammar as SchemaGrammar;
use DateTimeInterface;
use PDO;
use PDOStatement;

class Connection implements ConnectionInterface
{
    protected int $fetchMode = PDO::FETCH_OBJ;
    protected bool $recordsModified = false;
    protected QueryGrammar $queryGrammar;
    protected SchemaGrammar $schemaGrammar;

    /**
     * @param PDO|Closure $pdo
     * @param string $database
     * @param string $tablePrefix
     * @param array<string, mixed> $config
     */
    public function __construct(
        protected PDO|Closure $pdo,
        protected string $database = '',
        protected string $tablePrefix = '',
        protected array $config = []
    ) {
        $this->queryGrammar = new QueryGrammar($this);
        $this->schemaGrammar = new SchemaGrammar($this);
    }

    public function getName(): string
    {
        return $this->getConfig('driver');
    }

    public function getConfig(string $option = null): mixed
    {
        return $this->config[$option] ?? null;
    }
    public function getQueryGrammar(): QueryGrammar
    {
        return $this->queryGrammar;
    }

    public function getSchemaGrammar(): SchemaGrammar
    {
        return $this->schemaGrammar;
    }

    public function getSchemaBuilder(): SchemaBuilder
    {
        return new SchemaBuilder($this);
    }

    public function getPdo(): PDO
    {
        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }

        return $this->pdo;
    }

    public function query(): QueryBuilder
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar()
        );
    }

    public function select(string $query, array $bindings = []): array
    {
        $statement = $this->prepared(
            $this->getPdo()->prepare($query)
        );

        $this->bindValues($statement, $this->prepareBindings($bindings));

        $statement->execute();

        return $statement->fetchAll();
    }

    protected function prepared(PDOStatement $statement): PDOStatement
    {
        $statement->setFetchMode($this->fetchMode);

        return $statement;
    }

    public function prepareBindings(array $bindings): array
    {
        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($this->getQueryGrammar()->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    /**
     * @param PDOStatement $statement
     * @param array<int|string, mixed> $bindings
     *
     * @return void
     */
    public function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    is_int($value) => PDO::PARAM_INT,
                    is_resource($value) => PDO::PARAM_LOB,
                    default => PDO::PARAM_STR
                },
            );
        }
    }

    public function delete($query, $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function affectingStatement(string $query, array $bindings = []): int
    {
        $statement = $this->getPdo()->prepare($query);

        $this->bindValues($statement, $this->prepareBindings($bindings));

        $statement->execute();

        $this->recordsHaveBeenModified(
            ($count = $statement->rowCount()) > 0
        );

        return $count;
    }

    public function recordsHaveBeenModified(bool $value = true): void
    {
        if (! $this->recordsModified) {
            $this->recordsModified = $value;
        }
    }

    public function insert(string $query, array $bindings = []): bool
    {
        $statement = $this->getPdo()->prepare($query);
        $bindings = array_values($bindings);
        $this->bindValues($statement, $this->prepareBindings($bindings));

        $this->recordsHaveBeenModified();

        return $statement->execute();
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function statement(string $query, array $bindings = []): void
    {
        $statement = $this->getPdo()->prepare($query);
        $this->bindValues($statement, $this->prepareBindings($bindings));
        $statement->execute();
    }
}
