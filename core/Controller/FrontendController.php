<?php

namespace Core\Controller;

use Core\Block\Layout;

class FrontendController
{
    /**
     * @var Layout
     */
    protected Layout $layout;

    /**
     * @return $this
     */
    protected function prepareLayout(string $layoutName): FrontendController
    {
        $this->layout = new Layout($layoutName);
        $this->layout->prepare();

        return $this;
    }

    /**
     * @param string $blockName
     * @param array<string, mixed> $blockData
     *
     * @return void
     */
    protected function setBlockData(string $blockName, array $blockData): void
    {
        $this->layout->setBlockData($blockName, $blockData);
    }

    /**
     * @return void
     */
    protected function renderLayout(): void
    {
        $this->layout->render();
    }
}
