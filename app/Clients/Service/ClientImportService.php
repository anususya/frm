<?php

declare(strict_types=1);

namespace App\Clients\Service;

use App\Clients\Import\ClientsImport;
use App\Clients\Import\UploadFileImport;

class ClientImportService
{
    public function importFromImportDirectory(): bool
    {
        return (new ClientsImport())->run();
    }

    public function importFromUploadDirectory(): bool
    {
        return (new UploadFileImport())->run();
    }
}
