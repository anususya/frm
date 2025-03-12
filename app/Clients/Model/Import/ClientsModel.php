<?php

declare(strict_types=1);

namespace App\Clients\Model\Import;

use App\Clients\Import\ClientsImport;

class ClientsModel
{
    protected ClientsImport $clientsImport;

    public function __construct()
    {
        $this->setClientsImport();
    }

    protected function setClientsImport(): void
    {
        $this->clientsImport = new ClientsImport();
    }

    public function import(): bool
    {
        return $this->clientsImport->run();
    }
}
