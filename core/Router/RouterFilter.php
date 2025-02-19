<?php

declare(strict_types=1);

namespace Core\Router;

use Core\App\Superglobals\Variables;
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
        $method = strtoupper(Variables::getParamValue(Variables::TYPE_SERVER, 'REQUEST_METHOD'));
        $route = $this->current();

        if ($this->path === $route['path'] && in_array($method, $route['methods'])) {
            return true;
        }

        return false;
    }
}
