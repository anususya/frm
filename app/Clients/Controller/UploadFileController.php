<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientFileService;
use Core\Controller\AbstractController;

class UploadFileController extends AbstractController
{
    protected ClientFileService $clientFileService;
    public function __construct()
    {
        $this->clientFileService = new ClientFileService();

        parent::__construct();
    }
    public function index(): void
    {
        $this->view('clients/load.twig');
    }

    public function load(): void
    {
        $result = $this->clientFileService->upload('file', true);

        $this->view('clients/load.twig', ['load' => $result]);
    }
}
