<?php

declare(strict_types=1);

namespace Core\App;

use Core\Database\DatabaseServiceProvider;
use Core\Database\Migration\MigrationService;
use Core\Env\Env;
use Core\Router\Router;
use Exception;

// phpcs:ignore
class App
{
    public const BASE_APP_DIR = __DIR__ . '/../../';

    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function run(): void
    {
        Env::load();
        DatabaseServiceProvider::boot();
        MigrationService::run();
        $this->router->dispatch();
    }
}
