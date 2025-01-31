<?php

return [
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '192.168.3.8',
            'url' => 'DB_URL',
            'port' => '5432',
            'database' => 'test',
            'username' => 'admin',
            'password' => 'admin',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]
    ]
];
