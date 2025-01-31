<?php

namespace Core\Config;

use app;

class Config
{
    private const CONFIG_DIR = app::BASE_APP_DIR . '/config/';

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
        foreach (self::getConfigFiles() as $file) {
            $filePath = self::CONFIG_DIR . $file;
            $path_parts = pathinfo($filePath);

            if (isset($path_parts['extension']) && $path_parts['extension'] == 'php') {
                $loadConfig = include_once $filePath;
                foreach ($loadConfig as $key => $value) {
                    self::$config[$key] = $value;
                }
            }
        }
    }

    /**
     * @return array<string>
     */
    private static function getConfigFiles(): array
    {
        $dir = ['.','..'];
        $configFiles = [];

        $files = scandir(self::CONFIG_DIR);
        if ($files) {
            $configFiles = array_diff($files, $dir);
        }

        return $configFiles;
    }
}
