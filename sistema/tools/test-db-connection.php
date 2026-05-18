<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;

try {
    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $pdo = $connection->pdo();

    $statement = $pdo->query('SELECT 1');
    $result = $statement !== false ? $statement->fetchColumn() : false;

    if ($result === false || (int) $result !== 1) {
        echo 'ERROR: no fue posible verificar la conexión a base de datos.';
        exit(1);
    }

    echo 'OK: conexión a base de datos verificada correctamente.';
    exit(0);
} catch (Throwable $exception) {
    echo 'ERROR: no fue posible verificar la conexión a base de datos.';
    exit(1);
}