<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientFileService;
use Core\App\Superglobals;
use Core\Controller\AbstractController;

class GenerateController extends AbstractController
{
    protected ClientFileService $clientFileService;
    public function __construct()
    {
        $this->clientFileService = new ClientFileService();

        parent::__construct();
    }
    public function index(): void
    {
        $this->view('clients/generate.twig');
    }

    public function generate(): void
    {
        $count = Superglobals::Post->getParamValue('field');

        $this->json(['generate' => $this->clientFileService->generate((int) $count)]);
    }
}
