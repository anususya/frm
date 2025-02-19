<?php

declare(strict_types=1);

namespace App\Clients\Model\Import;

use App\Clients\Import\UploadFileImport;

class UploadFileModel extends ClientsModel
{
    protected function setClientsImport(): void
    {
        $this->clientsImport = new UploadFileImport();
    }
}
