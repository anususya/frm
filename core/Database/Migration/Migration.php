<?php

namespace logs\Env\DB\Migration;

use logs\Env\DB\DatabaseManager;
use logs\Env\Env;

class Migration
{
    protected $connection;

    protected $migrationFiles;
    public function __construct()
    {
        $name = 'pgsql';
        $databaseManager = new DatabaseManager();
        $this->connection = $databaseManager->connection($name);
    }

    public function isMigrationNeeded()
    {
        $version = Env::get('APP_VERSION');
        if ($version != '0.0.0') {
            return true;
        }
        return false;
    }

    protected function getMigrationFiles()
    {
        if (!$this->migrationFiles) {
            $dir = ['.','..'];
            $files = scandir('/var/www/html/migration');
            if ($files) {
                $this->migrationFiles  =  array_diff($files, $dir);
            }
        }
        return $this->migrationFiles;
    }

    public function runMigrations()
    {
        if ($this->getMigrationFiles()) {
            foreach ($this->getMigrationFiles() as $file) {
                $filePath = '/var/www/html/migration/' . $file;
                $path_parts = pathinfo($filePath);
                if ($path_parts['extension'] == 'sql') {
                    $fileNameParts = explode('-', $path_parts['filename']);
                    if ($fileNameParts[0] == 'upgrade') {
                        $script = file_get_contents($filePath);
                        if ($script === false or $script == '') {
                            continue;
                        }
                        $this->connection->execute($script);
                    }
                }
            }
        }
    }
}
