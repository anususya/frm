<?php

declare(strict_types=1);

namespace App\Clients\Service;

use App\Clients\Import\ClientsImport;
use App\Clients\Import\UploadFileImport;

class ClientImportService
{
    public function __construct(
        protected ClientsImport $clientsImport,
        protected UploadFileImport $uploadFileImport
    ) {
    }
    public function importFromImportDirectory(): bool
    {
        return $this->clientsImport->run();
    }

    public function importFromUploadDirectory(): bool
    {
        return $this->uploadFileImport->run();
    }
}
