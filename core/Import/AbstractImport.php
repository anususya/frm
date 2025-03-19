<?php

declare(strict_types=1);

namespace Core\Import;

use Core\Database\Noname\Model;
use RuntimeException;
use Core\Config\Config;

abstract class AbstractImport
{
    protected const IMPORT_TYPE = '';
    protected const IMPORT_NAME = '';
    protected const MODEL_CLASS = '';

    protected Model $model;

    /**
     * @var array<string, mixed>
     */
    protected array $importConfig = [];

    abstract public function run(): ?bool;

    public function __construct()
    {
        $this->importConfig = Config::get('import.' . $this->getImportName()) ?? [];
        $this->model = $this->loadModel();
    }

    public function getImportName(): string
    {
        return static::IMPORT_NAME;
    }

    protected function loadModel(): Model
    {
        if (!(static::MODEL_CLASS instanceof Model)) {
            throw new RuntimeException('Model not set or invalid');
        }

        return new (static::MODEL_CLASS);
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
