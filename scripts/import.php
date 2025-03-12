<?php

// phpcs:disable
if (php_sapi_name() == 'cli') {
    if (isset($argv[1])) {
        $importName = $argv[1];
        $className = match ($importName) {
            'clients' => '\app\Clients\Import\ClientsImport',
            default => ''
        };
        if (class_exists($className)) {
            $import = new $className();
        } else {
            echo 'This import type not found. Please, check import configuration file';
        }
    } else {
        echo 'You must define import type' . PHP_EOL;
    }
} else {
    echo 'This scrypt for console use only' . PHP_EOL;
}
// phpcs:enable
