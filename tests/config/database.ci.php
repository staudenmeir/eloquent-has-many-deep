<?php

return [
    'mysql' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => getenv('MYSQL_PORT'),
        'database' => 'test',
        'username' => 'root',
        'password' => 'password',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ],
];
