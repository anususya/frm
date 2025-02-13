<?php

namespace Core\Import;

use RuntimeException;
use Core\Config\Config;

abstract class AbstractImport
{
    /**
     * @var string
     */
    public string $importName = '';

    /**
     * @var string
     */
    protected string $importType = '';

    /**
     * @var array<string, mixed>
     */
    protected array $importConfig = [];

    /**
     * @return void
     */
    abstract protected function setImportName(): void;

    /**
     * @return void
     */
    abstract protected function setImportType(): void;

    /**
     * @return ?bool
     */
    abstract public function run(): ?bool;
    public function __construct()
    {
        $this->setImportName();
        $this->setImportType();
        $config = Config::getConfig('import');
        if ($config) {
            $this->importConfig = $config[$this->importName] ?? [];
        }
    }

    /**
     * @return void
     */
    protected function checkFormat(): void
    {
        if ($this->importConfig['format'] !== $this->importType) {
            throw new RuntimeException();
        }
    }

    /**
     * @return void
     */
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
