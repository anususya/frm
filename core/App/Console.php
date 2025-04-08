<?php

declare(strict_types=1);

namespace Core\App;

use Core\Config\Config;
use Core\Database\DatabaseServiceProvider;
use Core\DI\Container;
use Core\Env\Env;
use Exception;
use ReflectionException;
use Symfony\Component\Console\Application;

class Console
{
    protected Container $container;
    protected Application $application;

    public function __construct()
    {
        $this->container = new Container();
        $this->application = new Application();
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        DatabaseServiceProvider::boot();
        Env::load();

        $this->initCommands();
        $this->application->run();
    }

    /**
     * @throws ReflectionException
     */
    protected function initCommands(): void
    {
        $commands = Config::get('console.commands');
        foreach ($commands as $commandClass) {
            $this->application->add($this->container->make($commandClass)); // @phpstan-ignore-line
        }
    }
}
