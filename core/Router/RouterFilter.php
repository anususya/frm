<?php

declare(strict_types=1);

namespace Core\Router;

use Core\App\Superglobals;
use FilterIterator;
use Iterator;

// @phpstan-ignore missingType.generics
class RouterFilter extends FilterIterator
{
    public function __construct(
        private readonly string $path,
        Iterator $routes
    ) {
        parent::__construct($routes);
    }

    public function accept(): bool
    {
        if (!is_string($method = Superglobals::Server->getParamValue('REQUEST_METHOD'))) {
            return false;
        }

        $method = strtoupper($method);
        $route = $this->current();

        if ($this->path === $route['path'] && in_array($method, $route['methods'])) {
            return true;
        }

        return false;
    }
}
