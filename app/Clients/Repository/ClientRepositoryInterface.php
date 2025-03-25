<?php

declare(strict_types=1);

namespace App\Clients\Repository;

use Core\Database\Noname\Collection;
use Core\Repository\RepositoryInterface;

interface ClientRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array<string|int>  $ids
     *
     * @return Collection
     */
    public function findByIds(array $ids): Collection;

    /**
     * @param string|array<mixed> $searchParams
     *
     * @return Collection
     */
    public function findBy(string|array $searchParams): Collection;

    public function getClientsCountByField(string $field): Collection;
}
