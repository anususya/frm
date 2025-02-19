<?php

declare(strict_types=1);

namespace Core\Controller;

class PageNotFoundController extends FrontendController
{
    public function index(): void
    {
        $this->render('default/404');
    }
}
