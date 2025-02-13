<?php

namespace App\Clients\Controller;

use App\Clients\Model\Import\ClientsModel as ClientsImportModel;
use Core\Controller\FrontendController;

class ParseController extends FrontendController
{
    /**
     * @return void
     */
    public function index(): void
    {
        $importModel = new ClientsImportModel();
        $importResult = $importModel->import();

        $data = ['importResult' => $importResult];

        $this->prepareLayout('clients/parse');
        $this->setBlockData('search', $data);
        $this->renderLayout();
    }
}
