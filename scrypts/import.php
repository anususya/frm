<?php

// phpcs:disable
require_once(__DIR__ . '/../app/db.php');

if (php_sapi_name() == 'cli') {
    $config = require_once(__DIR__ . '/../config/import.php');
    if (isset($argv[1])) {
        $importName = $argv[1];
        if (!empty($config[$importName])) {
            $importConfig = $config[$importName];
            try {
                main();
                echo 'Import finished' . PHP_EOL;
            } catch (Exception $e) {
                echo 'Import failed' . PHP_EOL;
                //echo $e->getMessage();
            }
        } else {
            echo 'Import configuration not found' . PHP_EOL;
        }
    } else {
        echo 'You must define import type' . PHP_EOL;
    }
} else {
    echo 'This scrypt for console use only' . PHP_EOL;
}
// phpcs:enable
/**
 * @return void
 */
function main(): void
{
    global $importConfig;
    checkImportConfig();

    $functionName = match ($importConfig['format']) {
        'csv' => 'importCsvFile',
        default => throw new RuntimeException(
            'Can\'t import ' .
            $importConfig['format'] .
            ' format. Please, check import configuration file'
        ),
    };
    try {
        runInstallTableScript($importConfig['tableName']);
        $functionName();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

/**
 * @param string $tableName
 *
 * @return void
 */
function runInstallTableScript(string $tableName): void
{
    $filePath = __DIR__ . '/../migration/' . $tableName . '.sql';
    $script = file_get_contents($filePath);
    if ($script) {
        $connection = connectToDatabase();
        $connection->exec($script);
    }
}

/**
 * @return void
 */
function checkImportConfig(): void
{
    global $importConfig;

    if (
        empty($importConfig['tableName']) || empty($importConfig['fileName'])
        || empty($importConfig['columns']) || empty($importConfig['format'])
    ) {
        throw new RuntimeException('Import configuration is wrong. Please check import configuration file');
    }
}

/**
 * @return void
 * @throws Exception
 */
function importCsvFile(): void
{
    global $importConfig;

    $batchSize = 100;
    $counter = 0;
    $lines = [];

    if (($handle = @fopen(__DIR__ . '/../import/' . $importConfig['fileName'], "r")) !== false) {
        $connection = connectToDatabase();

        foreach (getLine($handle) as $line) {
            $counter++;
            $lines[] = $line;
            if ($counter % $batchSize == 0) {
                insert($connection, $importConfig['tableName'], $lines);
                $lines = [];
            }
        }
        insert($connection, $importConfig['tableName'], $lines);
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
function getLine($handle): Generator
{
    global $importConfig;
    $row = 1;

    while (($data = fgetcsv($handle, 1000)) !== false) {
        if ($row == 1) {
            if (!empty(array_diff($importConfig['columns'], $data))) {
                throw new RuntimeException('Please, check fields name in import file');
            }
            $row++;
            continue;
        }
        $row++;

        if (count($data) != count($importConfig['columns'])) {
            throw new RuntimeException('Please, check data in field ' . $row);
        }

        yield $data;
    }
}
