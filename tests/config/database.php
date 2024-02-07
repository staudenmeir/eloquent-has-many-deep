<?php

return [
    'mysql' => [
        'driver' => 'mysql',
        'host' => 'mysql',
        'port' => '3306',
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
