<?php

declare(strict_types=1);

namespace Core\Block;

use Core\App\App;
use LogicException;
use SplFileInfo;

class Block
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @var array<string, Block>
     */
    public array $childBlocks = [];

    /**
     * @param array<string, string> $attributes
     */
    public function __construct(
        public readonly array $attributes
    ) {
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
     * @return array<string, Block>
     */
    public function getChildBlocks(): array
    {
        return $this->childBlocks;
    }

    public function render(): void
    {
        $filePath = $this->attributes['file'] ?? '';
        $realPath = App::BASE_APP_DIR . '/frontend/front/' . $filePath;
        $fileInfo = new SplFileInfo($realPath);

        if ($fileInfo->isFile() && $fileInfo->isReadable()) {
            ob_start();
            include $realPath;
            $block = ob_get_clean();
        } else {
            throw new LogicException('File not found: ' . $realPath);
        }

        echo $block ?: '';
    }

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
