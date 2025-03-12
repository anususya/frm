<?php

declare(strict_types=1);

namespace Core\Database\Connectors;

use PDO;

class PostgresConnector extends Connector implements ConnectorInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @return PDO
     */
    public function connect(array $config): PDO
    {
        return $this->createConnection(
            $this->getDsn($config),
            $config,
            $this->getOptions($config)
        );
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        // First we will create the basic DSN setup as well as the port if it is
        // in the configuration options. This will give us the basic DSN we will
        // need to establish the PDO connections and return them back for use.
        extract($config, EXTR_SKIP);

        $host = isset($host) ? "host={$host};" : '';

        // Sometimes - users may need to connect to a database that has a different
        // name than the database used for "information_schema" queries. This is
        // typically the case if using "pgbouncer" type software when pooling.
        $database = $connect_via_database ?? $database ?? null;
        $port = $connect_via_port ?? $port ?? null;

        $dsn = "pgsql:{$host}dbname='{$database}'";

        // If a port was specified, we will add it to this Postgres DSN connections
        // format. Once we have done that we are ready to return this connection
        // string back out for usage, as this has been fully constructed here.
        if (! is_null($port)) {
            $dsn .= ";port={$port}";
        }

        if (isset($charset)) {
            $dsn .= ";client_encoding='{$charset}'";
        }

        // Postgres allows an application_name to be set by the user and this name is
        // used to when monitoring the application with pg_stat_activity. So we'll
        // determine if the option has been specified and run a statement if so.
        if (isset($application_name)) {
            $dsn .= ";application_name='" . str_replace("'", "\'", $application_name) . "'";
        }

        return $dsn;
    }
}
