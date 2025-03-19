<?php

declare(strict_types=1);

namespace Core\Database\Schema;

/**
 * @property bool $nullable
 * @property string $name
 * @property string $type
 * @property mixed $default
 * @property bool $change
 */
class ColumnDefinition
{
    /**
     * @var array<mixed>
     */
    protected array $attributes = [];

    /**
     * @param array<mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * @param array<mixed> $attributes
     *
     * @return $this
     */
    public function fill(array $attributes): ColumnDefinition
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function nullable(): bool
    {
        return $this->attributes['nullable'] = true;
    }
}
