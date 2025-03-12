<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Database\Noname\Model;

class DatabaseServiceProvider
{
    public static function boot(): void
    {
        $db = new DatabaseManager();
        Model::setConnectionResolver($db);
    }
}
