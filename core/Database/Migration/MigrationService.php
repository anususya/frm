<?php

declare(strict_types=1);

namespace Core\Database\Migration;

use Core\App\App;
use Core\App\Superglobals;
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

    /**
     * @return void
     * @throws Exception
     */
    public static function run(): void
    {
        (new self())->runMigrations();
    }
    public static function setConnectionResolver(ConnectionResolverInterface $resolver): void
    {
        self::$resolver = $resolver;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function runMigrations(): void
    {
        if (!$this->isTableExists('app')) {
            $this->execute('install', ['path' => self::MIGRATION_DIR . 'install.php']);
        }

        $to =  is_string(Superglobals::Env->getParamValue('APP_VERSION')) ?
            Superglobals::Env->getParamValue('APP_VERSION') : '';
        $from = $this->getCurrentVersion();

        if (! $migrationFiles = $this->getMigrationFiles($from, $to)) {
            return;
        }

        uasort($migrationFiles, function ($a, $b) {
            return version_compare($a['from'], $b['from']);
        });

        foreach ($migrationFiles as $name => $params) {
            if (version_compare($from, $to, '<') && version_compare($params['from'], $from, '=')) {
                $this->execute($name, $params);
                $from = $params['to'];
            }
        }

        $this->connection->query()->from('app')
            ->where('option', '=', 'version')
            ->update(['value' => $from]);
    }

    /**
     * @param string $name
     * @param array<string, mixed>  $params
     *
     * @return void
     * @throws Exception
     */
    protected function execute(string $name, array $params): void
    {
        $migrationClass = include_once $params['path'];

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

    public function getCurrentVersion(): string
    {
        $query = $this->connection->query();
        $option = $query->from('app')
            ->where('option', 'version')
            ->get()
            ->first();
        return $option->value;
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getMigrationFiles(string $from, string $to): array
    {
        $migrationFiles = [];

        foreach (new DirectoryIterator(self::MIGRATION_DIR) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->getExtension() != 'php') {
                continue;
            }

            $name = $fileInfo->getBasename('.' . $fileInfo->getExtension());

            if ($name == 'install') {
                continue;
            }

            list($fileFrom, $fileTo) = explode('-', $name);

            if (version_compare($fileFrom, $from, '<') || version_compare($fileTo, $to, '>')) {
                continue;
            }

            $migrationFiles[$name] = [
                'from' => $from,
                'to' => $to,
                'path' => $fileInfo->getRealPath()
            ];
        }

        return $migrationFiles;
    }
}
