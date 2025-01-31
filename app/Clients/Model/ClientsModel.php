<?php

namespace App\Clients\Model;

use Core\Config\Config as Config;
use Core\Database\DB as DB;
use Exception;

class ClientsModel
{
    /**
     * @param array<string> $params $params
     *
     * @return array<list<string>>|null
     * @throws Exception
     */
    public function getClients(array $params): ?array
    {
        $config = Config::getConfig('import');
        if (isset($config['clients']['tableName'])) {
            $tableName = $config['clients']['tableName'];
            $connection = DB::getConnection();
            try {
                return $connection?->selectAll($tableName, $params);
            } catch (Exception $e) {
                return [];
            }
        } else {
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
        $config = Config::getConfig('import');
        if (!isset($config['clients'])) {
            return [];
        } else {
            $importConfig = $config['clients'];
        }

        foreach ($requestParams as $key => $value) {
            if (str_ends_with($key, '_to')) {
                $key = str_replace('_to', '', $key);
                $requestParams[$key]['to'] = $value;
            }
            if (str_ends_with($key, '_from')) {
                $key = str_replace('_from', '', $key);
                $requestParams[$key]['from'] = $value;
            }
        }

        $params = array_filter($requestParams, function ($key) use ($importConfig) {
            return in_array($key, $importConfig['columns']);
        }, ARRAY_FILTER_USE_KEY);

        return $params;
    }
}
