<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Model\ClientsModel;
use Core\App\Superglobals\Variables;
use Core\Controller\FrontendController;

class SearchController extends FrontendController
{
    public function index(): void
    {
        $clientsModel = new ClientsModel();
        $searchParams = $clientsModel->convertRequestParams(Variables::get(Variables::TYPE_GET));
        $searchResult = $clientsModel->getClients($searchParams);

        $blockData = [
            'search' => [
                'searchResult' => $searchResult
            ]
        ];

        $this->render('clients/search', $blockData);
    }
}
