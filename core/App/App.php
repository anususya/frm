<?php

declare(strict_types=1);

namespace Core\App;

use Core\Router\Router;

// phpcs:ignore
class App
{
    public const BASE_APP_DIR = __DIR__ . '/../../';

    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    public function run(): void
    {
        $this->router->dispatch();
    }
}
