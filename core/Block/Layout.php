<?php

declare(strict_types=1);

namespace Core\Block;

use Core\App\App;
use SimpleXMLElement;
use SplFileInfo;

class Layout
{
    /**
     * @var array<string, Block>
     */
    protected array $blocks = [];

    /**
     * @var array<string, mixed>
     */
    protected array $blockData = [];

    public function __construct(
        protected readonly string $layoutName
    ) {
    }

    public function prepare(): void
    {
        $filePath = $this->layoutName . '.xml';
        $layout = $this->loadLayoutFile($filePath);
        if ($layout) {
            $this->parse($layout);
        }
    }

    public function render(): void
    {
        $this->mergeBlocksData($this->blocks);

        foreach ($this->blocks as $block) {
            $block->render();
        }
    }

    private function loadLayoutFile(string $filePath): false|SimpleXMLElement
    {
        $realFilePath = App::BASE_APP_DIR . '/frontend/layouts/' . $filePath;
        $fileInfo = new SplFileInfo($realFilePath);

        if ($fileInfo->isFile() && $fileInfo->isReadable()) {
            return simplexml_load_file($realFilePath);
        }

        return false;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function setBlockData(array $data): void
    {
        foreach ($data as $blockName => $blockData) {
            $this->blockData[$blockName] = $blockData;
        }
    }

    /**
     * @param array<string, Block> $blocks
     *
     * @return void
     */
    private function mergeBlocksData(array $blocks): void
    {
        foreach ($blocks as $name => $block) {
            if (array_key_exists($name, $this->blockData)) {
                $block->setData($this->blockData[$name]);
            } elseif ($block->childBlocks) {
                $this->mergeBlocksData($block->childBlocks);
            }
        }
    }

    /**
     * @param SimpleXMLElement $layout
     *
     * @return void
     */
    private function parse(SimpleXMLElement $layout): void
    {
        foreach ($layout as $element) {
            if (!$element) {
                continue;
            }

            $block = $this->createElement($element);

            if (!$block) {
                continue;
            }

            $this->blocks = array_merge($this->blocks, $block);
        }
    }

    /**
     * @param SimpleXMLElement $element
     *
     * @return array<string, Block>|null
     */
    private function createElement(SimpleXMLElement $element): ?array
    {
        $attributes = [];

        foreach ($element->attributes() as $key => $value) {
            $attributes[$key] = (string)$value;
        }

        $blockClass = $attributes['class'] ?? null;
        $blockName = $attributes['name'] ?? null;

        if (!$blockClass || !$blockName || !class_exists($blockClass)) {
            return null;
        }

        $block = new $blockClass($attributes);

        if (!($block instanceof Block)) {
            return null;
        }

        $childBlocks = [];

        if (isset($element->childBlock->block)) {
            foreach ($element->childBlock->block as $child) {
                $childBlock = $this->createElement($child);

                if ($childBlock) {
                    $childBlocks = array_merge($childBlocks, $childBlock);
                }
            }
        }

        $block->setChildBlocks($childBlocks);

        return array($attributes['name'] => $block);
    }
}
