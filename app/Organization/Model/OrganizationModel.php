<?php

namespace App\Organization\Model;

use Core\Database\Noname\Model;

class OrganizationModel extends Model
{
    protected string $table = 'organization';
    protected ?string $connection = 'pgsql';
}
