<?php

namespace Core\Block;

use App;
use SimpleXMLElement;
use SplFileInfo;

class Layout
{
    /**
     * @var string
     */
    protected string $layoutName;

    /**
     * @var string
     */
    protected string $controllerName;

    /**
     * @var array<string, Block>
     */
    public array $blocks = [];

    /**
     * @var array<string, mixed>
     */
    protected array $blockData = [];

    /**
     * @param string $layoutName
     */
    public function __construct(string $layoutName)
    {
        $this->layoutName = $layoutName;
    }

    /**
     * @return void
     */
    public function prepare(): void
    {
        $filePath = $this->layoutName . '.xml';
        $layout = $this->loadLayoutFile($filePath);
        if ($layout) {
            $this->parse($layout);
        }
    }

    /**
     * @return void
     */
    public function render(): void
    {
        $this->mergeBlocksData($this->blocks);

        foreach ($this->blocks as $block) {
            $block->render();
        }
    }

    /**
     * @param string $filePath
     *
     * @return false|SimpleXMLElement
     */
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
     * @param string $blockName
     * @param mixed $data
     *
     * @return void
     */
    public function setBlockData(string $blockName, mixed $data): void
    {
        $this->blockData[$blockName] = $data;
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
            if ($element) {
                $block = $this->createElement($element);
                if ($block) {
                    $this->blocks = array_merge($this->blocks, $block);
                }
            }
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

        $blockClass = $attributes['class'] ?? '';
        $blockName = $attributes['name'] ?? '';
        if ($blockClass && $blockName && class_exists($blockClass)) {
            $block = new $blockClass($attributes);
            if ($block instanceof Block) {
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

        return null;
    }
}
