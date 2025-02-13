<?php

namespace App\Clients\Controller;

use App\Clients\Model\Import\UploadFileModel as ImportUploadFileModel;
use App\Clients\Model\UploadFileModel;
use Core\Controller\FrontendController;

class UploadFileController extends FrontendController
{
    /**
     * @return void
     */
    public function index(): void
    {
        $this->prepareLayout('clients/load');
        $this->renderLayout();
    }
    public function load(): void
    {
        $uploadModel = new UploadFileModel();
        $uploadResult = $uploadModel->upload('file');
        $importResult = false;
        if ($uploadResult) {
            $importFile = new ImportUploadFileModel();
            $importResult = $importFile->import();
        }

        $this->prepareLayout('clients/load');
        $this->setBlockData('load', ['uploadResult' => $uploadResult, 'importResult' => $importResult]);
        $this->renderLayout();
    }
}
