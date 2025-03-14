<?php

declare(strict_types=1);

function my_custom_autoloader(string $path_to_class): void
{
    $fileMap = include_once 'autoload_classmap.php';

    if (isset($fileMap[$path_to_class]) && file_exists($fileMap[$path_to_class])) {
        require_once $fileMap[$path_to_class];
    } else {
        $path_to_class = str_replace('\\', '/', $path_to_class);
        $path_parts = explode('/', $path_to_class);
        $path_parts[0] = strtolower($path_parts[0]);
        $path = implode('/', $path_parts);
        $file = __DIR__ . '/' . $path . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// phpcs:disable
spl_autoload_register('my_custom_autoloader');
// phpcs:enable
