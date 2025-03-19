<?php

declare(strict_types=1);

namespace Core\Database\Migration;

use Core\Database\Connection;
use Core\Database\DatabaseManager;
use Exception;

class Migration
{
    protected static DatabaseManager $resolver;
    protected Connection $connection;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->connection = self::$resolver->connection('');
    }

    public static function setConnectionResolver(DatabaseManager $resolver): void
    {
        self::$resolver = $resolver;
    }
}
