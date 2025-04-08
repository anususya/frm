<?php

namespace Core\Repository;

use Core\Database\Noname\Collection;
use Core\Database\Noname\Model;

interface RepositoryInterface
{
    public function findAll(): Collection;
    public function findById(string|int $id): ?Model;

    /**
     * @param array<string, mixed> $data
     *
     * @return Model
     */
    public function save(array $data): Model;

    /**
     * @param array<mixed> $data
     *
     * @return bool
     */
    public function saveMany(array $data): bool;

    /**
     * @param int|string $id
     * @param array<string, mixed> $data
     *
     * @return Model|null
     */
    public function update(int|string $id, array $data): Model|null;

    public function delete(int|string $id): bool;

    public function deleteAll(): void;
}
