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
     * @var string
     */
    protected string $moduleName;

    /**
     * @var string
     */
    protected string $controllerName;

    public function __construct(string $moduleName, string $controllerName)
    {
        $this->moduleName = $moduleName;
        $this->controllerName = $controllerName;
    }

    /**
     * @return $this
     */
    protected function prepareLayout(): FrontendController
    {
        $this->layout = new Layout($this->moduleName, $this->controllerName);
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
