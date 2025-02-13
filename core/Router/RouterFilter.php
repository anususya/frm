<?php

namespace Core\Router;

use FilterIterator;
use Iterator;

// @phpstan-ignore missingType.generics
class RouterFilter extends FilterIterator
{
    /**
     * @var string
     */
    private string $path;

    /**
     * @param Iterator $routes
     * @param string   $path
     */
    public function __construct(Iterator $routes, string $path)
    {
        parent::__construct($routes);
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function accept(): bool
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $route = $this->current();
        if ($this->path === $route['path'] && in_array($method, $route['methods'])) {
            return true;
        }
        return false;
    }
}
