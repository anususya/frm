<?php

declare(strict_types=1);

namespace Core\Database\Query;

class Expression
{
    public function __construct(
        protected mixed $value
    ) {
    }
    public function getValue(): mixed
    {
        return $this->value;
    }
}
