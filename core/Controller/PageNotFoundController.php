<?php

namespace Core\Controller;

class PageNotFoundController extends FrontendController
{
    /**
     * @return void
     */
    public function index(): void
    {
        $this->prepareLayout('default/404');
        $this->renderLayout();
    }
}
