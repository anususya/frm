<?php

/**
 * @return false|string
 * @throws Exception
 */
function getData(): false|string
{
    require_once __DIR__ . '/db.php';
    $config = require_once __DIR__ . '/../config/import.php';
    $importConfig = $config['clients'];

    $connection = connectToDatabase();
    $getParams = $_GET;

    foreach ($getParams as $key => $value) {
        if (str_ends_with($key, '_to')) {
            $key = str_replace('_to', '', $key);
            $getParams[$key]['to'] = $value;
        }
        if (str_ends_with($key, '_from')) {
            $key = str_replace('_from', '', $key);
            $getParams[$key]['from'] = $value;
        }
    }
    $params = array_filter($getParams, function ($key) use ($importConfig) {
        return in_array($key, $importConfig['columns']);
    }, ARRAY_FILTER_USE_KEY);

    $data = selectAll($connection, $importConfig['tableName'], $params);

    return json_encode($data);
}
