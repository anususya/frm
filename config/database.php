<?php

use Core\App\Superglobals;

return [
    'default_connection' => 'pgsql',
    'connections' => [
        'pgsql' => [
            'driver' => Superglobals::Env->getParamValue('DB_DRIVER') ?? 'pgsql',
            'host' => Superglobals::Env->getParamValue('DB_HOST') ?? '127.0.0.1',
            'url' => 'DB_URL',
            'port' => Superglobals::Env->getParamValue('DB_PORT') ?? '5432',
            'database' => Superglobals::Env->getParamValue('DB_NAME') ?? 'postgres',
            'username' => Superglobals::Env->getParamValue('DB_USER') ?? 'root',
            'password' => Superglobals::Env->getParamValue('DB_PASS') ?? 'root',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]
    ]
];
