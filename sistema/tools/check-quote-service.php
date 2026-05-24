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
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Services\QuoteService;

try {
    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $repository = new QuoteRepository($connection->pdo());
    $service = new QuoteService($repository);

    $totalQuotes = $service->countQuotes();
    $recentQuotes = $service->getRecentQuotes(5);

    outputOk('QuoteService disponible.');
    outputOk("Total de cotizaciones: {$totalQuotes}.");
    outputOk('Lectura reciente ejecutada con límite 5.');

    if ($recentQuotes !== []) {
        $firstQuote = $recentQuotes[0];
        $firstQuoteId = isset($firstQuote['id']) ? (int) $firstQuote['id'] : 0;
        $detail = $service->getQuoteDetail($firstQuoteId);

        if ($detail !== null) {
            outputOk("Detalle de cotización #{$firstQuoteId} leído correctamente.");
        }
    }

    exit(0);
} catch (\Throwable $exception) {
    outputError('No fue posible verificar QuoteService.');
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
