<?php

namespace Core\Config;

use App;
use DirectoryIterator;

class Config
{
    private const CONFIG_DIR = App::BASE_APP_DIR . '/config/';

    /**
     * @var array<string, mixed>|null
     */
    private static ?array $config = null;

    /**
     * @return array<string, mixed>|null
     */
    public static function getConfig(string $name): ?array
    {
        if (is_null(self::$config)) {
            self::loadConfig();
        }
        return self::$config[$name] ?? null;
    }

    /**
     * @return void
     */
    private static function loadConfig(): void
    {
        foreach (new DirectoryIterator(self::CONFIG_DIR) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if ($fileInfo->getExtension() === 'php' && $fileInfo->isReadable()) {
                $loadConfig = include_once $fileInfo->getPathname();
                foreach ($loadConfig as $key => $value) {
                    self::$config[$key] = $value;
                }
            }
        }
    }
}
