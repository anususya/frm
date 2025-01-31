<?php

namespace Core\Import;

use App;
use Core\Database\DB as DB;
use Exception;
use Generator;
use RuntimeException;

abstract class AbstractImportCsv extends AbstractImport
{
    /**
     * @return void
     */
    protected function setImportType(): void
    {
        $this->importType = 'csv';
    }

    /**
     * @return ?bool
     */
    public function run(): ?bool
    {
        $this->checkImportConfig();
        $this->checkFormat();
        try {
            $this->import();

            return true;
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function import(): void
    {
        $batchSize = 100;
        $counter = 0;
        $lines = [];

        if (($handle = @fopen(App::BASE_APP_DIR . '/import/' . $this->importConfig['fileName'], "r")) !== false) {
            $connection = DB::getConnection();

            foreach ($this->getLine($handle) as $line) {
                $counter++;
                $lines[] = $line;
                if ($counter % $batchSize == 0) {
                    $connection?->insert($this->importConfig['tableName'], $this->importConfig['columns'], $lines);
                    $lines = [];
                }
            }
            $connection?->insert($this->importConfig['tableName'], $this->importConfig['columns'], $lines);
            fclose($handle);
        } else {
            throw new RuntimeException('Can\'t open the import file');
        }
    }

    /**
     * @param resource $handle
     *
     * @return Generator
     */
    protected function getLine($handle): Generator
    {
        $row = 1;

        while (($data = fgetcsv($handle, 1000)) !== false) {
            if ($row == 1) {
                if (!empty(array_diff($this->importConfig['columns'], $data))) {
                    throw new RuntimeException(
                        'Please, check fields name in import file'
                    );
                }
                $row++;
                continue;
            }
            $row++;

            if (count($data) != count($this->importConfig['columns'])) {
                throw new RuntimeException(
                    'Please, check data in field ' . $row
                );
            }

            yield $data;
        }
    }
}
