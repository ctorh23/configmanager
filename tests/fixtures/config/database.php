<?php

return [
    'connections' => [
        'pgsql' => [
            'host' => 'pgsql.acme.com',
            'port' => 5432,
        ],
        'mariadb' => [
            'host' => 'mariadb.acme.com',
            'port' => 3306,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]
    ],
    'migrations' => [
        'table' => 'migrations'
    ]
];
