<?php

namespace Core\Block;

use app;

class Block
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @var array<string, string>
     */
    public array $attributes;

    /**
     * @var array<string, Block>
     */
    public array $childBlocks = [];


    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param array<string, Block> $childBlocks
     *
     * @return void
     */
    public function setChildBlocks(array $childBlocks): void
    {
        $this->childBlocks = $childBlocks;
    }

    /**
     * @return Block[]
     */
    public function getChildBlocks(): array
    {
        return $this->childBlocks;
    }
    /**
     * @return void
     */
    public function render(): void
    {
        ob_start();
        $realPath = app::BASE_APP_DIR . '/frontend/front/' . $this->attributes['file'] ?? '';
        include $realPath;
        $block = ob_get_clean();
        echo $block ?: '';
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getData(string $name): mixed
    {
        if ($this->data && array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAllData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }
}
