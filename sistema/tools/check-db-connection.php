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

    if ((int) $result !== 1) {
        echo 'Error de conexión controlado';
        exit(1);
    }

    echo 'Conexión OK';
    exit(0);
} catch (Throwable $exception) {
    echo 'Error de conexión controlado';
    exit(1);
}
