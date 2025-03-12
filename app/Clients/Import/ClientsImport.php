<?php

declare(strict_types=1);

namespace App\Clients\Import;

use Core\App\App;
use Core\Database\DB;
use Core\Import\AbstractImportCsv;
use Core\Log\Log;
use Exception;

class ClientsImport extends AbstractImportCsv
{
    protected const IMPORT_NAME = 'clients';

    public function run(): bool
    {
        try {
            $this->runInstallTableScript($this->importConfig['tableName']);
            return parent::run();
        } catch (Exception $e) {
            Log::write($e->getMessage());
        }

        return false;
    }

    /**
     * @param string $tableName
     *
     * @return void
     * @throws Exception
     */
    public function runInstallTableScript(string $tableName): void
    {
        $filePath = App::BASE_APP_DIR . '/migration/' . $tableName . '.sql';
        $script = file_get_contents($filePath);

        if ($script === false) {
            return;
        }

        $connection = DB::getConnection();
        $connection?->exec($script);
    }
}
