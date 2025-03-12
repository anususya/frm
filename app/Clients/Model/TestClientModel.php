<?php

declare(strict_types=1);

namespace App\Clients\Model;

use Core\Database\Noname\Model;

class TestClientModel extends Model
{
    protected ?string $connection = 'pgsql';
    protected string $table = 'clients';
    protected string $primaryKey = 'client_id';
}
