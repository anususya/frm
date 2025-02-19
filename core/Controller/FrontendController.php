<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Block\Layout;

class FrontendController
{
    protected Layout $layout;

    protected function prepareLayout(string $layoutName): FrontendController
    {
        $this->layout = new Layout($layoutName);
        $this->layout->prepare();

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function setBlockData(array $data): void
    {
        $this->layout->setBlockData($data);
    }

    protected function renderLayout(): void
    {
        $this->layout->render();
    }

    /**
     * @param string $layoutName
     * @param null|array<string, mixed> $data
     *
     * @return void
     */
    protected function render(string $layoutName, array $data = null): void
    {
        $this->prepareLayout($layoutName);

        if ($data) {
            $this->setBlockData($data);
        }

        $this->renderLayout();
    }
}
