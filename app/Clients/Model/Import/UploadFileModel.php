<?php

namespace App\Clients\Model\Import;

use App\Clients\Import\UploadFileImport;

class UploadFileModel extends ClientsModel
{
    /**
     * @return void
     */
    protected function setClientsImport(): void
    {
        $this->clientsImport = new UploadFileImport();
    }
}
