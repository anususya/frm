<?php

declare(strict_types=1);

namespace App\Clients\Import;

use App\Clients\Model\ClientModel;
use Core\Import\AbstractImportCsv;

class ClientsImport extends AbstractImportCsv
{
    protected const IMPORT_NAME = 'clients';
    protected const MODEL_CLASS  = ClientModel::class;
}
