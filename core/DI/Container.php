<?php

declare(strict_types=1);

namespace Core\DI;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

class Container
{
    /**
     * @var array<string, string>
     */
    protected array $bindings = [];

    public function bind(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    // Resolve a class and inject its dependencies

    /**
     * @throws ReflectionException
     */
    public function make(string $abstract): object
    {
        if (isset($this->bindings[$abstract])) {
            return $this->build($this->bindings[$abstract]);
        }

        return $this->build($abstract);
    }

    /**
     * @throws ReflectionException
     */
    protected function build(string $concrete): object
    {
        $reflection = new ReflectionClass($concrete); // @phpstan-ignore-line
        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $concrete();
        }

        // Get constructor parameters and resolve their dependencies
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();

            if ($dependency instanceof ReflectionNamedType) {
                $dependencies[] = $this->make($dependency->getName());
            }
        }

        // Create the instance with dependencies
        return $reflection->newInstanceArgs($dependencies);
    }
}
