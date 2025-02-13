<?php

use Core\Router\Router;

// phpcs:ignore
class App
{
    public const BASE_APP_DIR = __DIR__;

    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->router->dispatch();
    }
}
