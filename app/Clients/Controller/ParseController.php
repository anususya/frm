<?php

namespace App\Clients\Controller;

use App\Clients\Import\ClientsImport;
use Core\Controller\FrontendController;

class ParseController extends FrontendController
{
    /**
     * @return void
     */
    public function index(): void
    {
        $import = new ClientsImport();
        if ($import->run()) {
            $importResult = 1;
        } else {
            $importResult = 0;
        }

        $data = ['importResult' => $importResult];

        $this->prepareLayout();
        $this->setBlockData('search', $data);
        $this->renderLayout();
    }
}
