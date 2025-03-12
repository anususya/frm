<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Model\Import\UploadFileModel as ImportUploadFileModel;
use App\Clients\Model\UploadFileModel;
use Core\Controller\FrontendController;

class UploadFileController extends FrontendController
{
    public function index(): void
    {
        $this->render('clients/load');
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

        $blockData = [
            'load' => [
                'uploadResult' => $uploadResult,
                'importResult' => $importResult
            ]
        ];

        $this->render('clients/load', $blockData);
    }
}
