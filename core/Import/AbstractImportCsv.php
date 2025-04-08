<?php

declare(strict_types=1);

namespace Core\Import;

use Core\App\App;
use Core\Log\Log;
use Exception;
use Generator;
use RuntimeException;

abstract class AbstractImportCsv extends AbstractImport
{
    protected const IMPORT_TYPE = 'csv';

    protected string $importDirectory = App::BASE_APP_DIR . '/import/';

    public function run(bool $rewriteOldData = true): bool
    {
        $this->checkImportConfig();
        $this->checkFormat();

        try {
            if ($rewriteOldData) {
                $this->repository->deleteAll();
            }

            $this->import();
            return true;
        } catch (Exception $e) {
            Log::write($e->getMessage(), 'error');
        }

        return false;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function import(): void
    {
        $batchSize = 100;
        $counter = 0;
        $lines = [];
        $columns = array_map(function ($column) {
            return strtolower($column);
        }, $this->importConfig['columns']);

        if (($handle = @fopen($this->importDirectory . $this->importConfig['fileName'], "r")) !== false) {
            foreach ($this->getLine($handle) as $line) {
                $counter++;
                $lines[] = array_combine($columns, $line);
                if (($counter % $batchSize) == 0) {
                    $this->repository->saveMany($lines);
                    $lines = [];
                }
            }

            if ($lines) {
                $this->repository->saveMany($lines);
            }

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
