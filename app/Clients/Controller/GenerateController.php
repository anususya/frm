<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Service\ClientFileService;
use Core\App\Superglobals;
use Core\Controller\AbstractController;
use Core\HTTP\Response\ResponseFactory;

class GenerateController extends AbstractController
{
    public function __construct(
        protected ClientFileService $clientFileService,
        ResponseFactory $response
    ) {

        parent::__construct($response);
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
