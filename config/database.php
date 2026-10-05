<?php

require_once __DIR__ . '/../app/Core/helpers.php';

return [
    'default' => env('DB_CONNECTION', 'mysql'), // 'mysql' or 'sqlite'
    'connections' => [
        'mysql' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int)env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'alaz_store'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD') !== null ? (string)env('DB_PASSWORD') : 'root',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'sqlite' => [
            'database' => env('DB_SQLITE_PATH', __DIR__ . '/../database/database.sqlite'),
        ]
    ]
];
