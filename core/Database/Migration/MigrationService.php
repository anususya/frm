<?php

declare(strict_types=1);

namespace Core\Database\Migration;

use Core\App\App;
use Core\Database\ConnectionInterface;
use Core\Database\ConnectionResolverInterface;
use DirectoryIterator;
use Exception;

class MigrationService
{
    public const MIGRATION_DIR = App::BASE_APP_DIR . 'migration/';
    protected static ConnectionResolverInterface $resolver;
    protected ConnectionInterface $connection;

    public function __construct()
    {
        $this->connection = self::$resolver->connection('');
    }

    public static function setConnectionResolver(ConnectionResolverInterface $resolver): void
    {
        self::$resolver = $resolver;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function run(): void
    {
        if (! $migrationFiles = $this->getMigrationFiles()) {
            return;
        }

        if ($this->isTableExists('migrations')) {
            $executedFiles = $this->getExecutedMigrations();

            foreach ($executedFiles as $executedFile) {
                if (isset($migrationFiles[$executedFile['name']])) {
                    unset($migrationFiles[$executedFile['name']]);
                }
            }
        }

        try {
            foreach ($migrationFiles as $name => $path) {
                $this->execute($name, $path);
                $runFiles[] = ['name' => $name];
            }
        } finally {
            if (isset($runFiles)) {
                $this->connection->query()->from('migrations')->insert($runFiles);
            }
        }
    }

    /**
     * @param string $name
     * @param string $path
     *
     * @return void
     * @throws Exception
     */
    protected function execute(string $name, string $path): void
    {
        $migrationClass = include_once $path;

        if (! $migrationClass) {
            throw new Exception('Migration file "' . $name . '" not found.');
        }

        if (! $migrationClass instanceof Migration || ! method_exists($migrationClass, 'up')) {
            return;
        }

        $migration = new $migrationClass();
        $migration->up();
    }

    public function isTableExists(string $table): bool
    {
        $query = $this->connection->query();
        $result = $query->from('information_schema.TABLES')
            ->where('table_name', $table)
            ->get();

        return $result->count() > 0;
    }

    /**
     * @return array<mixed>
     */
    public function getExecutedMigrations(): array
    {
        $query = $this->connection->query();

        return $query->from('migrations')->get()->jsonSerialize();
    }

    /**
     * @return array<string, string>
     */
    protected function getMigrationFiles(): array
    {
        $migrationFiles = [];

        foreach (new DirectoryIterator(self::MIGRATION_DIR) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->getExtension() != 'php') {
                continue;
            }

            $name = $fileInfo->getBasename('.' . $fileInfo->getExtension());
            $migrationFiles[$name] = $fileInfo->getRealPath();
        }

        asort($migrationFiles);

        return $migrationFiles;
    }
}
