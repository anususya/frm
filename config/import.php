<?php

return [
    'import' => [
        'clients' => [
            'format' => 'csv',
            'tableName' => 'clients',
            'fileName' => 'clients.csv',
            'columns' => ['country', 'city', 'is_active', 'gender', 'birth_date',
                          'salary','has_children', 'family_status', 'registration_date']
        ]
    ]
];
