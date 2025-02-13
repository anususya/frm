<?php

namespace App\Clients\Import;

use App;
use Core\Database\DB as DB;
use Core\Import\AbstractImportCsv;
use Core\Log\Log;
use Exception;
use PDOException;

/**
 * extends AbstractImportCsv
 */
class ClientsImport extends AbstractImportCsv
{
    /**
     * @return void
     */
    protected function setImportName(): void
    {
        $this->importName = 'clients';
    }

    /**
     * @return ?bool
     */
    public function run(): ?bool
    {
        try {
            $this->runInstallTableScript($this->importConfig['tableName']);
            return parent::run();
        } catch (Exception $e) {
            Log::write($e->getMessage());
        }

        return null;
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
        if ($script) {
            $connection = DB::getConnection();
            try {
                $connection?->exec($script);
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }
}
