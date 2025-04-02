<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientService;
use Core\App\Superglobals;
use Core\Controller\AbstractController;
use Core\HTTP\Response\ResponseFactory;

class ClientController extends AbstractController
{
    public function __construct(
        protected ClientService $clientService,
        ResponseFactory $response
    ) {
        parent::__construct($response);
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
