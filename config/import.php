<?php

return [
    'import' => [
        'clients' => [
            'format' => 'csv',
            'tableName' => 'clients',
            'fileName' => 'clients.csv',
            'columns' => ['country', 'city', 'isActive', 'gender', 'birthDate',
                          'salary','hasChildren', 'familyStatus', 'registrationDate']
        ]
    ]
];
