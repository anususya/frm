<?php

declare(strict_types=1);

namespace Core\Database\Noname;

use ArgumentCountError;
use stdClass;

class Collection
{
    public function __construct(// @phpstan-ignore-line
        protected array $items = []
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function map(callable $callback): static
    {
        return new static(self::map1($this->items, $callback)); // @phpstan-ignore-line
    }

    /**
     * @param array<string, mixed>   $array
     * @param callable $callback
     *
     * @return array<string, mixed>
     */
    public static function map1(array $array, callable $callback): array
    {
        $keys = array_keys($array);

        try {
            $items = array_map($callback, $array, $keys);
        } catch (ArgumentCountError) {
            $items = array_map($callback, $array);
        }

        return array_combine($keys, $items);
    }

    public function implode(?string $value, ?string $glue = ', '): string
    {
        return implode($value ?? '', $this->items);
    }

    public function first(mixed $default = null): mixed
    {
        if (! empty($this->items)) {
            foreach ($this->items as $item) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof Model) {
                return $value->toArray();
            }

            if ($value instanceof StdClass) {
                return (array) $value;
            }

            return $value;
        }, $this->all());
    }

    public function toJson(): string|false
    {
        return json_encode($this->jsonSerialize());
    }
}
