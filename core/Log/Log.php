<?php

namespace Core\Log;

use App;

class Log
{
    public const LOG_DIR_PATH = App::BASE_APP_DIR . '/logs';

    public const DEFAULT_LOG_FILE = self::LOG_DIR_PATH . '/default.log';

    /**
     * @param string $message
     * @param string $level
     *
     * @return void
     */
    public static function write(string $message, string $level = 'info'): void
    {
        if (file_exists(self::DEFAULT_LOG_FILE)) {
            chmod(self::DEFAULT_LOG_FILE, 0777);
        }
        $fp = fopen(self::DEFAULT_LOG_FILE, 'a');
        if ($fp !== false) {
            $message = date('Y-m-d H:i:s') . ' ' . $level . ': ' . $message . PHP_EOL;
            fwrite($fp, $message);
            fclose($fp);
        }
    }
}
