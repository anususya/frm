<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Model\ClientsModel;
use Core\App\Superglobals;
use Core\Controller\FrontendController;

class SearchController extends FrontendController
{
    public function index(): void
    {
        $clientsModel = new ClientsModel();
        $searchParams = $clientsModel->convertRequestParams(Superglobals::Get->getParamsValue());
        $searchResult = $clientsModel->getClients($searchParams);

        $blockData = [
            'search' => [
                'searchResult' => $searchResult
            ]
        ];

        $this->render('clients/search', $blockData);
    }
}
