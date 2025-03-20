<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Config\Config;
use Core\Database\Connectors\ConnectionFactory;
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

    /**
     * @throws Exception
     */
    public function connection(?string $name): Connection
    {
        $name = $name ?: $this->getDefaultConnection();

        if (! isset($this->connections[$name])) {
            if ($config = $this->configuration($name)) {
                $this->connections[$name] = $this->factory->make($config);
            } else {
                throw new Exception('DB connection named "' . $name . '" not found');
            }
        }

        return $this->connections[$name];
    }

    public function getDefaultConnection(): string
    {
        return Config::get('default_connection');
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
