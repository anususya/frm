<?php

declare(strict_types=1);

namespace Core\Database\Connectors;

use Core\Database\Connection;
use Closure;
use InvalidArgumentException;

class ConnectionFactory
{
    /**
     * @param array<string, mixed> $config
     *
     * @return Connection
     */
    public function make(array $config): Connection
    {
        $pdo = $this->createPdoResolver($config);

        return $this->createConnection(
            $config['driver'],
            $pdo,
            $config['database'],
            $config['prefix'],
            $config
        );
    }

    /**
     * @param string  $driver
     * @param Closure $connection
     * @param string  $database
     * @param string  $prefix
     * @param array<string, mixed>   $config
     *
     * @return Connection
     */
    protected function createConnection(
        string $driver,
        Closure $connection,
        string $database,
        string $prefix = '',
        array $config = []
    ): Connection {
        return match ($driver) {
            'pgsql' => new Connection($connection, $database, $prefix, $config),
            default => throw new InvalidArgumentException("Unsupported driver [{$driver}]."),
        };
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return Closure
     */
    protected function createPdoResolver(array $config): Closure
    {
        return fn () => $this->createConnector($config)->connect($config);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return Connector
     */
    public function createConnector(array $config): Connector
    {
        if (! isset($config['driver'])) {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        return match ($config['driver']) {
            'pgsql' => new PostgresConnector(),
            default => throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]."),
        };
    }
}
