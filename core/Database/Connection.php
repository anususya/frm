<?php

namespace Core\Database;

use Core\Database\Query\Builder as QueryBuilder;
use Core\Database\Schema\Schema as Schema;
use Core\Database\Query\Grammars\Grammar as QueryGrammar;
use PDOStatement;
use PDO;

class Connection implements ConnectionInteface
{
    protected $fetchMode = PDO::FETCH_OBJ;
    //protected $fetchMode = PDO::FETCH_ASSOC;


    /**
     * The active PDO connection.
     *
     * @var \PDO|\Closure
     */
    protected $pdo;

    /**
     * The name of the connected database.
     *
     * @var string
     */
    protected $database;

    /**
     * The table prefix for the connection.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected $config = [];

    protected $schema;

    /**
     * Indicates if changes have been made to the database.
     *
     * @var bool
     */
    protected $recordsModified = false;
    protected $queryGrammar;

    /**
     * Create a new database connection instance.
     *
     * @param  \PDO|\Closure  $pdo
     * @param  string  $database
     * @param  string  $tablePrefix
     * @param  array  $config
     * @return void
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;
        $this->database = $database;
        $this->tablePrefix = $tablePrefix;
        $this->config = $config;

        $this->useDefaultQueryGrammar();

        //$this->useDefaultPostProcessor();
    }

    public function getName()
    {
        return $this->getConfig('driver');
    }

    public function getConfig($option = null)
    {
        return $this->config[$option] ?? null;
    }

    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    protected function getDefaultQueryGrammar()
    {
        return new QueryGrammar($this);
    }
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    public function getPdo()
    {
        if ($this->pdo instanceof \Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }

        return $this->pdo;
    }

    public function execute($sql)
    {
        if (!$this->pdo) {
            throw new Exception('Connection not established');
        }
        $this->getPdo()->exec($sql);
    }

    /**
     * Get a new query builder instance.
     */
    public function query()
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar()
            //$this->getPostProcessor()
        );
    }

    public function select($query, $bindings = []): array
    {
        $statement = $this->prepared(
            $this->getPdo()->prepare($query)
        );

        $this->bindValues($statement, $this->prepareBindings($bindings));

        $statement->execute();

        return $statement->fetchAll($this->fetchMode);
    }

    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);

        return $statement;
    }

    public function prepareBindings(array $bindings): array
    {
        return $bindings;
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into the actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    public function bindValues($statement, $bindings)
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

    public function affectingStatement($query, $bindings = []): int
    {
        // For update or delete statements, we want to get the number of rows affected
        // by the statement and return that back to the developer. We'll first need
        // to execute the statement and then we'll use PDO to fetch the affected.
        $statement = $this->getPdo()->prepare($query);

        $this->bindValues($statement, $this->prepareBindings($bindings));

        $statement->execute();

        $this->recordsHaveBeenModified(
            ($count = $statement->rowCount()) > 0
        );

        return $count;
    }

    public function recordsHaveBeenModified($value = true)
    {
        if (! $this->recordsModified) {
            $this->recordsModified = $value;
        }
    }

    public function insert($query, $bindings = []): bool
    {
        $statement = $this->getPdo()->prepare($query);
        $bindings = array_values($bindings);
        $this->bindValues($statement, $this->prepareBindings($bindings));

        $this->recordsHaveBeenModified();
        $res = $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function update($query, $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }
}
