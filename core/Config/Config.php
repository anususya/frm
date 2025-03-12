<?php

declare(strict_types=1);

namespace Core\Config;

use Core\App\App;
use DirectoryIterator;

class Config
{
    private const CONFIG_DIR = App::BASE_APP_DIR . '/config/';

    /**
     * @var array<string, mixed>|null
     */
    private static ?array $config = null;

    public static function get(string $path): mixed
    {
        if (is_null(self::$config)) {
            self::load();
        }

        if (empty(self::$config)) {
            return null;
        }

        $keys = explode('.', $path);

        return self::getValue(self::$config, $keys);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, string> $keys
     *
     * @return mixed
     */
    private static function getValue(array $config, array $keys): mixed
    {
        $key = array_shift($keys);

        if ($key && isset($config[$key])) {
            if (empty($keys)) {
                return $config[$key];
            }

            if (is_array($config[$key])) {
                return self::getValue($config[$key], $keys);
            }
        }

        return null;
    }

    private static function load(): void
    {
        foreach (new DirectoryIterator(self::CONFIG_DIR) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->getExtension() !== 'php' || !$fileInfo->isReadable()) {
                continue;
            }

            $loadConfig = include_once $fileInfo->getPathname();

            foreach ($loadConfig as $key => $value) {
                self::$config[$key] = $value;
            }
        }
    }
}
