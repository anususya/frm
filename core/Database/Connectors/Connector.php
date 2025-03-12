<?php

declare(strict_types=1);

namespace Core\Database\Connectors;

use PDO;

class Connector
{
    /**
     * @param string $dsn
     * @param array<string, mixed> $config
     * @param array<mixed> $options
     *
     * @return PDO
     */
    public function createConnection(string $dsn, array $config, array $options): PDO
    {
        [$username, $password] = [
            $config['username'] ?? null, $config['password'] ?? null,
        ];

        return $this->createPdoConnection(
            $dsn,
            $username,
            $password,
            $options
        );
    }

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array<mixed> $options
     *
     * @return PDO
     */
    protected function createPdoConnection(string $dsn, string $username, string $password, array $options): PDO
    {
        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<mixed>
     */
    public function getOptions(array $config): array
    {
        return $config['options'] ?? [];
    }
}
