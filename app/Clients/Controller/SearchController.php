<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientService;
use Core\Controller\AbstractController;
use Core\HTTP\Response\ResponseFactory;

class SearchController extends AbstractController
{
    public function __construct(
        protected ClientService $clientService,
        ResponseFactory $response
    ) {
        parent::__construct($response);
    }
    public function index(): void
    {
        $clients = $this->clientService->getAllClients();

        $this->view('clients/clients.twig', ['clients' => $clients->toJson()]);
    }

    public function diagram(): void
    {
        $this->view('clients/diagram.twig');
    }
}
