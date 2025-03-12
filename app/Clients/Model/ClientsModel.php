<?php

declare(strict_types=1);

namespace App\Clients\Model;

use Core\Config\Config;
use Core\Database\DB;
use Exception;

class ClientsModel
{
    /**
     * @param array<string> $params $params
     *
     * @return null|array<list<string>>
     */
    public function getClients(array $params): ?array
    {
        $tableName = Config::get('import.clients.tableName');

        if (!$tableName) {
            return [];
        }

        try {
            $connection = DB::getConnection();
            return $connection?->selectAll($tableName, $params);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $requestParams
     *
     * @return array<string, mixed>
     */
    public function convertRequestParams(array $requestParams): array
    {
        $importConfig = Config::get('import.clients');

        if (!$importConfig) {
            return [];
        }
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
            $result[] = [$key,  $operator,  $value];
        }
        return $result;
        return array_filter(
            $requestParams,
            static fn ($key) => in_array($key, $importConfig['columns']),
            ARRAY_FILTER_USE_KEY
        );
    }
}
