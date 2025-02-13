<?php

namespace App\Clients\Controller;

use App\Clients\Model\ClientsModel;
use Core\Controller\FrontendController;
use Exception;

class SearchController extends FrontendController
{
    /**
     * @return void
     * @throws Exception
     */
    public function index(): void
    {
        $clientsModel = new ClientsModel();
        $searchParams = $clientsModel->convertRequestParams($_GET);
        $searchResult = $clientsModel->getClients($searchParams);

        $data = ['searchResult' => $searchResult];

        $this->prepareLayout('clients/search');
        $this->setBlockData('search', $data);
        $this->renderLayout();
    }
}
