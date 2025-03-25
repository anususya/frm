<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientImportService;
use Core\Controller\AbstractController;

class ParseController extends AbstractController
{
    protected ClientImportService $clientImportService;

    public function __construct()
    {
        $this->clientImportService = new ClientImportService();

        parent::__construct();
    }
    public function index(): void
    {
        $this->view(
            'clients/parse.twig',
            ['importResult' => $this->clientImportService->importFromImportDirectory()]
        );
    }
}
