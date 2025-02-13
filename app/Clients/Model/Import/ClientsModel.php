<?php

namespace App\Clients\Model\Import;

use App\Clients\Import\ClientsImport;

class ClientsModel
{
    /**
     * @var ClientsImport
     */
    protected ClientsImport $clientsImport;
    public function __construct()
    {
        $this->setClientsImport();
    }

    /**
     * @return void
     */
    protected function setClientsImport(): void
    {
        $this->clientsImport = new ClientsImport();
    }

    /**
     * @return bool
     */
    public function import(): bool
    {
        if ($this->clientsImport->run()) {
            return true;
        } else {
            return false;
        }
    }
}
