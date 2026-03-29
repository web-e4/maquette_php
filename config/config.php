<?php

return [
    'db' => [
        'dsn'      => 'mysql:host=127.0.0.1;dbname=myapp;charset=utf8mb4',
        'user'     => 'root',
        'password' => 'root',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
    'twig' => [
        'templates_path' => dirname(__DIR__) . '/Views',
        'cache_path'     => dirname(__DIR__) . '/var/cache/twig',
        'debug'          => true,
    ],
];
