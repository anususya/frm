<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Database\Connectors\ConnectionFactory;
use Core\Config\Config;
use Exception;

class DatabaseManager implements ConnectionResolverInterface
{
    /**
     * @var array<string, Connection>
     */
    protected array $connections = [];
    public function __construct(
        protected ConnectionFactory $factory = new ConnectionFactory()
    ) {
    }

    public function connection(string $name): Connection
    {
        if (! isset($this->connections[$name])) {
            $config = $this->configuration($name);
            if ($config) {
                $this->connections[$name] = $this->factory->make($config);
            } else {
                throw new Exception('DB connection named "' . $name . '" not found');
            }
        }

        return $this->connections[$name];
    }

    /**
     * @param string $name
     *
     * @return null|array<string, mixed>
     */
    protected function configuration(string $name): ?array
    {
        return Config::get('connections.' . $name);
    }
}
