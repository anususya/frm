<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;
use Core\Config\Config;
use RuntimeException;
use Exception;
use PDOException;
use Core\Log\Log;

class DB
{
    private static ?DB $instance = null;

    private ?PDO $pdo = null;

    public static function getConnection(): ?DB
    {
        if (!self::$instance) {
            try {
                self::$instance = new DB();
            } catch (Exception $e) {
                Log::write($e->getMessage());
            }
        }

        return self::$instance;
    }

    private function __construct()
    {
        if (!$dbConfig = Config::get('connections.pgsql')) {
            throw new RuntimeException('pgsql configuration not found');
        } elseif (
            !isset($dbConfig['host']) || !isset($dbConfig['port'])
            || !isset($dbConfig['database'])
            || !isset($dbConfig['username'])
            || !isset($dbConfig['password'])
        ) {
            throw new RuntimeException(
                'Pgsql configuration is wrong. Please check database configuration file'
            );
        }

        try {
            $dsn = 'pgsql:host=' . $dbConfig['host'] . ';port=' .
                $dbConfig['port'] . ';dbname=' . $dbConfig['database'] . ';';
            // make a database connection
            $this->pdo = new PDO(
                $dsn,
                $dbConfig['username'],
                $dbConfig['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            Log::write($e->getMessage());
        }
    }

    /**
     * @param string              $table
     * @param array<string>       $columns
     * @param array<list<string>> $data
     *
     * @return void
     * @throws Exception
     */
    public function insert(string $table, array $columns, array $data): void
    {
        $values = [];
        $placeholder = '(' . implode(',', array_fill(0, count($columns), '?'))
            . ')';

        if (!$this->pdo) {
            throw new Exception('Connection not established');
        }

        $query = 'INSERT INTO ' . $table . ' (';

        foreach ($columns as $value) {
            $query .= $value . ',';
        }
        $query = substr($query, 0, -1);
        $query .= ') VALUES ';

        foreach ($data as $line) {
            $query .= $placeholder . ',';
            $values = array_merge($values, $line);
        }

        $query = substr($query, 0, -1);
        $query .= ';';
        $statement = $this->pdo->prepare($query);
        $statement->execute($values);
    }

    /**
     * @param string        $table
     * @param array<string> $params
     *
     * @return array<list<string>>
     * @throws Exception
     */
    public function selectAll(string $table, array $params): array
    {
        if (!$this->pdo) {
            throw new Exception('Connection not established');
        }

        $values = [];
        $query = 'SELECT * FROM ' . $table;

        if ($params) {
            $result = $this->addParamsToQuery($params);
            $query .= $result['clause'] ? ' WHERE ' . $result['clause'] : '';
            $values = $result['values'];
        }

        $statement = $this->pdo->prepare($query);

        try {
            $statement->execute($values);
        } catch (PDOException $e) {  // phpcs:ignore
            //throw new Exception($e->getMessage());
            return [];
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<mixed> $params
     *
     * @return array<string, mixed>
     */
    private function addParamsToQuery(array $params): array
    {
        $values = [];
        $clause = '';

        foreach ($params as $key => $variables) {
            if (empty($variables)) {
                continue;
            }

            $clause .= '( ';

            if (is_array($variables)) {
                [$subQuery, $subValues] = $this->addRangeVariables($key, $variables);

                if (empty($subQuery)) {
                    $clause = substr($clause, 0, -2);
                    continue;
                }

                $clause .= $subQuery;
                $values = array_merge($values, $subValues);
            } else {
                foreach (explode(',', $variables) as $variable) {
                    $clause .= $key . ' = ?';
                    $clause .= ' OR ';
                    $values[] = trim($variable);
                }

                $clause = substr($clause, 0, -4);
            }

            $clause .= ')';
            $clause .= ' AND ';
        }

        $clause = substr($clause, 0, -4);

        return ['clause' => $clause, 'values' => $values];
    }

    /**
     * @param string $key
     * @param array<mixed> $variables
     *
     * @return array<mixed>
     */
    private function addRangeVariables(string $key, array $variables): array
    {
        $clause = '';
        $values = [];

        if ($variables['from'] && $variables['to']) {
            $clause .= $key . ' BETWEEN ? AND ?';
            $values[] = $variables['from'];
            $values[] = $variables['to'];

            return [$clause, $values];
        }

        if ($variables['from']) {
            $clause .= $key . " > ?";
            $values[] = $variables['from'];

            return [$clause, $values];
        }

        if ($variables['to']) {
            $clause .= $key . ' < ?';
            $values[] = $variables['to'];

            return [$clause, $values];
        }

        return [$clause, $values];
    }

    /**
     * @param string $query
     *
     * @return void
     * @throws Exception
     */
    public function exec(string $query): void
    {
        if (!$this->pdo) {
            throw new Exception('Connection not established');
        }

        $this->pdo->exec($query);
    }
}
