<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientFileService;
use Core\Controller\AbstractController;
use Core\HTTP\Response\ResponseFactory;

class UploadFileController extends AbstractController
{
    public function __construct(
        protected ClientFileService $clientFileService,
        ResponseFactory $response
    ) {
        parent::__construct($response);
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
