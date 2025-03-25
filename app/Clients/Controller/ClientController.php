<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientService;
use Core\App\Superglobals;
use Core\Controller\AbstractController;

class ClientController extends AbstractController
{
    protected ClientService $clientService;

    public function __construct()
    {
        $this->clientService = new ClientService();

        parent::__construct();
    }

    public function get(): void
    {
        $searchParams = Superglobals::Get->getParamsValue();

        $this->json($this->clientService->searchByParams($searchParams, 'array')); //@phpstan-ignore-line
    }

    public function getCountByField(): void
    {
        $field = is_string(Superglobals::Post->getParamValue('field')) ?
            Superglobals::Post->getParamValue('field') : '';

        $this->json($this->clientService->getClientCountByField($field, 'array')); //@phpstan-ignore-line
    }
}
