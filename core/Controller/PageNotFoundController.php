<?php

declare(strict_types=1);

namespace Core\Controller;

class PageNotFoundController extends AbstractController
{
    public function index(): void
    {
        $this->view('base/404.twig', [], 404, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
