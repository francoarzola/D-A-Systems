<?php

declare(strict_types=1);

return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'usuariohosting_dasystems_internal',
    'username' => 'usuariohosting_dasystems_app',
    'password' => 'CAMBIAR_EN_CPANEL',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
