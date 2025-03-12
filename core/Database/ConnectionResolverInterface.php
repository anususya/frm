<?php

declare(strict_types=1);

namespace Core\Database;

interface ConnectionResolverInterface
{
    public function connection(string $name): Connection;
}
