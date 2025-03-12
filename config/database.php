<?php

use Core\App\Superglobals;

function env($key, $default = null)
{
    return Superglobals::Env->getParamValue($key) ?? $default;
}

return [
    'connections' => [
        'pgsql' => [
            'driver' => env('DB_DRIVER', 'pgsql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'url' => 'DB_URL',
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_NAME', 'postgres'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASS', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]
    ]
];
