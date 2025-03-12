<?php

declare(strict_types=1);

namespace Core\Import;

use RuntimeException;
use Core\Config\Config;

abstract class AbstractImport
{
    protected const IMPORT_TYPE = '';
    protected const IMPORT_NAME = '';

    /**
     * @var array<string, mixed>
     */
    protected array $importConfig = [];

    abstract public function run(): ?bool;

    public function __construct()
    {
        $this->importConfig = Config::get('import.' . $this->getImportName()) ?? [];
    }

    public function getImportName(): string
    {
        return static::IMPORT_NAME;
    }

    protected function checkFormat(): void
    {
        if ($this->importConfig['format'] !== static::IMPORT_TYPE) {
            throw new RuntimeException();
        }
    }

    protected function checkImportConfig(): void
    {
        if (
            empty($this->importConfig['tableName']) || empty($this->importConfig['fileName'])
            || empty($this->importConfig['columns']) || empty($this->importConfig['format'])
        ) {
            throw new RuntimeException('Import configuration is wrong. Please check import configuration file');
        }
    }
}
