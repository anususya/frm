<?php

declare(strict_types=1);

namespace App\Clients\Repository;

use App\Clients\Model\ClientModel;
use Core\Database\Noname\Collection;
use Core\Database\Noname\Model;

class ClientRepository implements ClientRepositoryInterface
{
    public function findAll(): Collection
    {
        return ClientModel::all();
    }

    /**
     * @param array<string|int>  $ids
     *
     * @return Collection
     */
    public function findByIds(array $ids): Collection
    {
        return ClientModel::findMany($ids);
    }
    public function findById(string|int $id): ?Model
    {
        return ClientModel::find($id);
    }

    /**
     * @param string|array<mixed> $searchParams
     *
     * @return Collection
     */
    public function findBy(string|array $searchParams): Collection
    {
        return ClientModel::where($searchParams)->get();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Model
     */
    public function save(array $data): Model
    {
        return ClientModel::create($data);
    }

    /**
     * @param array<mixed> $data
     *
     * @return bool
     */
    public function saveMany(array $data): bool
    {
        return ClientModel::insert($data);
    }

    public function update(int|string $id, array $data): Model|null
    {
        if ($client = ClientModel::find($id)) {
            $client->update($data);

            return $client;
        }

        return null;
    }
    public function delete(int|string $id): bool
    {
        if ($client = ClientModel::find($id)) {
            return $client->delete();
        }

        return false;
    }

    public function getClientsCountByField(string $field): Collection
    {
        $query = ClientModel::query()->newQuery();
        return $query->from('clients')
            ->select([$field, $query->raw('count(*) as count')])
            ->groupBy($field)
            ->get();
    }

    public function deleteAll(): void
    {
        ClientModel::truncate();
    }
}
