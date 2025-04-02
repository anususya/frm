<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientImportService;
use Core\Controller\AbstractController;
use Core\HTTP\Response\ResponseFactory;

class ParseController extends AbstractController
{
    public function __construct(
        protected ClientImportService $clientImportService,
        ResponseFactory $response
    ) {
        parent::__construct($response);
    }
    public function index(): void
    {
        $this->view(
            'clients/parse.twig',
            ['importResult' => $this->clientImportService->importFromImportDirectory()]
        );
    }
}
