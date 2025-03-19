<?php

declare(strict_types=1);

namespace App\Clients\Model;

use Core\Config\Config;
use Core\Database\Noname\Model;

class ClientModel extends Model
{
    protected ?string $connection = 'pgsql';
    protected string $table = 'clients';
    protected string $primaryKey = 'client_id';

    /**
     * @param array<string, mixed> $requestParams
     *
     * @return array<int, mixed>
     */
    public static function convertRequestParams(array $requestParams): array
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

            $parts = preg_split('/[A-Z]/', $key) ?: null;
            $key = implode('_', $parts);

            $result[] = [strtolower($key),  $operator,  $value];
        }

        return $result;
    }
}
