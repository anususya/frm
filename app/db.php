<?php

// phpcs:disable
$pdo = null;
// phpcs:enable

/**
 * @return PDO
 */
function connectToDatabase(): PDO
{
    global $pdo;

    if ($pdo) {
        return $pdo;
    }

    $configFile = __DIR__ . '/../config/database.php';
    if (!file_exists($configFile)) {
        throw new RuntimeException('Missing database configuration file');
    }
    $config = require $configFile;
    $dbConfig = $config['connections']['pgsql'] ?? null;

    if (!$dbConfig) {
        throw new RuntimeException('pgsql configuration not found');
    } elseif (
        !isset($dbConfig['host']) ||
        !isset($dbConfig['port']) ||
        !isset($dbConfig['database']) ||
        !isset($dbConfig['username']) ||
        !isset($dbConfig['password'])
    ) {
        throw new RuntimeException('Pgsql configuration is wrong. Please check database configuration file');
    }

    try {
        $dsn = 'pgsql:host=' . $dbConfig['host'] . ';port=' .
            $dbConfig['port'] . ';dbname=' . $dbConfig['database'] . ';';
        // make a database connection
        $pdo = new PDO(
            $dsn,
            $dbConfig['username'],
            $dbConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        die($e->getMessage());
    }

    return $pdo;
}

/**
 * @param ?PDO    $pdo
 * @param string $table
 * @param array<list<string>>  $data
 *
 * @return void
 * @throws Exception
 */
function insert(?PDO $pdo, string $table, array $data): void
{
    global $importConfig;
    $values = [];
    $columns = $importConfig['columns'];
    $placeholder = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';

    if (!$pdo) {
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
    $statement = $pdo->prepare($query);
    $statement->execute($values);
}

/**
 * @param ?PDO          $pdo
 * @param string        $table
 * @param array<string> $params
 *
 * @return array<list<string>>
 * @throws Exception
 */
function selectAll(?PDO $pdo, string $table, array $params): array
{
    if (!$pdo) {
        throw new Exception('Connection not established');
    }

    $values = [];
    $query = 'SELECT * FROM ' . $table;

    if ($params) {
        $result = addParamsToQuery($params);
        $query .= $result['clause'] ? ' WHERE ' . $result['clause'] : '';
        $values = $result['values'];
    }

    $statement = $pdo->prepare($query);
    try {
        $statement->execute($values);
    } catch (PDOException $e) {  // phpcs:ignore
        //throw new Exception($e->getMessage());
        return [];
    }

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param array<int, mixed> $params
 *
 * @return array<string, mixed>
 */
function addParamsToQuery(array $params): array
{
    $values = [];
    $clause = '';

    foreach ($params as $key => $variables) {
        if (empty($variables)) {
            continue;
        }

        $clause .= '( ';

        if (is_array($variables)) {
            if ($variables['from'] && $variables['to']) {
                $clause .=  $key . ' BETWEEN ? AND ?';
                $values[] = $variables['from'];
                $values[] = $variables['to'];
            } else {
                if ($variables['from']) {
                    $clause .= $key . " > ?";
                    $values[] = $variables['from'];
                } elseif ($variables['to']) {
                    $clause .= $key . ' < ?';
                    $values[] = $variables['to'];
                } else {
                    $clause = substr($clause, 0, -2);
                    continue;
                }
            }
        } else {
            foreach (explode(',', $variables) as $variable) {
                $clause .=  $key . ' = ?';
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
