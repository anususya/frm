<?php

declare(strict_types=1);

namespace Core\Env;

use Core\App\App;
use Dotenv\Dotenv;

class Env
{
    public static function load(): void
    {
        $dotenv = Dotenv::createMutable(App::BASE_APP_DIR);
        $dotenv->safeLoad();
    }
}
