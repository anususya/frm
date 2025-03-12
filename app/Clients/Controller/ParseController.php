<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Model\Import\ClientsModel as ClientsImportModel;
use Core\Controller\FrontendController;

class ParseController extends FrontendController
{
    public function index(): void
    {
        $importModel = new ClientsImportModel();
        $importResult = $importModel->import();

        $blockData = [
            'search' => [
                'importResult' => $importResult
            ]
        ];

        $this->render('clients/parse', $blockData);
    }
}
