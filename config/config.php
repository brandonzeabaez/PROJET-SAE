<?php

declare(strict_types=1);

// lecture du  fichier .env 
$env = parse_ini_file(BASE_PATH . '/.env');

// infos de connexion 
return [
    'database' => [
        'host'     => $env['DB_HOST'],
        'port'     => 3306,
        'name'     => $env['DB_NAME'],
        'user'     => $env['DB_USER'],
        'password' => $env['DB_PASS'],
        'charset'  => 'utf8mb4',
    ],
];