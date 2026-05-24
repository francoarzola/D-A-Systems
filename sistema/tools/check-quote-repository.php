<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;

try {
    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $repository = new QuoteRepository($connection->pdo());

    $totalQuotes = $repository->countAll();
    $recentQuotes = $repository->findRecent(5);

    outputOk('QuoteRepository disponible.');
    outputOk("Total de cotizaciones: {$totalQuotes}.");
    outputOk('Lectura reciente ejecutada con limite 5.');

    foreach ($recentQuotes as $quote) {
        $id = isset($quote['id']) ? (int) $quote['id'] : 0;
        $status = isset($quote['estado']) ? (string) $quote['estado'] : 'sin_estado';
        $number = isset($quote['numero_cotizacion']) && $quote['numero_cotizacion'] !== null
            ? (string) $quote['numero_cotizacion']
            : 'sin_numero';

        outputOk("Cotizacion #{$id}: {$number} / {$status}.");
    }

    exit(0);
} catch (\Throwable $exception) {
    outputError('No fue posible verificar QuoteRepository.');
    exit(1);
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
