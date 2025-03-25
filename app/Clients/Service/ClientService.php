<?php

declare(strict_types=1);

namespace App\Clients\Service;

use App\Clients\Repository\ClientRepository;
use Core\Database\Noname\Collection;

class ClientService
{
    protected ClientRepository $repository;
    public function __construct()
    {
        $this->repository = new ClientRepository();
    }

    public function getAllClients(): Collection
    {
        return $this->repository->findAll();
    }

    /**
     * @param string $field
     * @param string $returnType
     *
     * @return Collection|array<mixed>
     */
    public function getClientCountByField(string $field, string $returnType = ''): array|Collection
    {
        $clients = $this->repository->getClientsCountByField($field);

        if ($returnType === 'array') {
            return $clients->jsonSerialize();
        }

        return $clients;
    }
    /**
     * @param array<string, mixed> $searchParams
     * @param string $returnType
     *
     * @return Collection|array<mixed>
     */
    public function searchByParams(array $searchParams, string $returnType = ''): array|Collection
    {
        $clients = $this->repository->findBy($this->convertRequestParams($searchParams));

        if ($returnType === 'array') {
            return $clients->jsonSerialize();
        }

        return $clients;
    }

    /**
     * @param array<string, mixed> $requestParams
     *
     * @return array<int, mixed>
     */
    public function convertRequestParams(array $requestParams): array
    {
        $result = [];
        $operator = '=';

        foreach ($requestParams as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (str_ends_with($key, '_to')) {
                $operator = '<=';
                $key = str_replace('_to', '', $key);
                $requestParams[$key]['to'] = $value;
            }

            if (str_ends_with($key, '_from')) {
                $operator = '>=';
                $key = str_replace('_from', '', $key);
                $requestParams[$key]['from'] = $value;
            }

            $parts = preg_split('/[A-Z]/', $key) ?: null;
            $key = implode('_', $parts);

            $result[] = [strtolower($key),  $operator,  $value];
        }

        return $result;
    }
}
