<?php

namespace Core\Database;

class Collection
{
    protected $items;
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }

        return $items;
    }

    public function all()
    {
        return $this->items;
    }

    public function map(callable $callback)
    {
        return new static(self::map1($this->items, $callback));
    }

    public static function map1(array $array, callable $callback)
    {
        $keys = array_keys($array);

        try {
            $items = array_map($callback, $array, $keys);
        } catch (ArgumentCountError) {
            $items = array_map($callback, $array);
        }

        return array_combine($keys, $items);
    }

    public function implode($glue = ', ')
    {
        return implode($value ?? '', $this->items);
    }

    public function first($default = null)
    {
        if (empty($this->items)) {
            return $default;
        }

        foreach ($this->items as $item) {
            return $item;
        }
    }
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            $value->toArray();
        }, $this->all());
    }

    public function toJson()
    {
        return json_encode($this->jsonSerialize());
    }
}