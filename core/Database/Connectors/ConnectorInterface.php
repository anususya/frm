<?php

declare(strict_types=1);

namespace Core\Database\Connectors;

use PDO;

interface ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param  array<string, mixed>  $config
     * @return PDO
     */
    public function connect(array $config): PDO;
}
