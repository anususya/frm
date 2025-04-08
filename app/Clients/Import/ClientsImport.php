<?php

declare(strict_types=1);

namespace App\Clients\Import;

use App\Clients\Repository\ClientRepository;
use Core\Import\AbstractImportCsv;

class ClientsImport extends AbstractImportCsv
{
    protected const IMPORT_NAME = 'clients';
    protected const MODEL_REPOSITORY  = ClientRepository::class;
}
