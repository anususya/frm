<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientService;
use Core\Controller\AbstractController;

class SearchController extends AbstractController
{
    protected ClientService $clientService;
    public function __construct()
    {
        $this->clientService = new ClientService();

        parent::__construct();
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
