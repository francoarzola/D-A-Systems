<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$basePath = dirname(__DIR__);
$quoteRepositoryPath = $basePath . '/app/Repositories/QuoteRepository.php';
$quoteNumberRepositoryPath = $basePath . '/app/Repositories/QuoteNumberRepository.php';
$quoteServicePath = $basePath . '/app/Services/QuoteService.php';
$publicIssueEndpointPath = $basePath . '/public/cotizacion-emitir.php';

$quoteRepository = readFileOrFail($quoteRepositoryPath, 'QuoteRepository.php');
$quoteNumberRepository = readFileOrFail($quoteNumberRepositoryPath, 'QuoteNumberRepository.php');
$quoteService = readFileOrFail($quoteServicePath, 'QuoteService.php');

$quoteRepositoryFragments = [
    'issueDraft',
    'beginTransaction',
    'commit',
    'rollBack',
    'FOR UPDATE',
    'QuoteNumberRepository',
    'reserveNextNumberInCurrentTransaction',
    'numero_cotizacion',
    'emitida',
    'borrador',
];

foreach ($quoteRepositoryFragments as $fragment) {
    assertContains($quoteRepository, $fragment, "QuoteRepository.php no contiene {$fragment}");
}

$quoteServiceFragments = [
    'issueDraft',
    'success',
    'numero_cotizacion',
    'emitida',
];

foreach ($quoteServiceFragments as $fragment) {
    assertContains($quoteService, $fragment, "QuoteService.php no contiene {$fragment}");
}

$quoteNumberRepositoryFragments = [
    'reserveNextNumberInCurrentTransaction',
    'ON DUPLICATE KEY UPDATE',
    'FOR UPDATE',
];

foreach ($quoteNumberRepositoryFragments as $fragment) {
    assertContains($quoteNumberRepository, $fragment, "QuoteNumberRepository.php no contiene {$fragment}");
}

if (is_file($publicIssueEndpointPath)) {
    outputError('No debe existir sistema/public/cotizacion-emitir.php.');
    exit(1);
}

$forbiddenFragments = [
    '$_POST',
    '$_GET',
    'AJAX',
    'API JSON',
    'MAX(',
];

foreach ([
    'QuoteRepository.php' => $quoteRepository,
    'QuoteService.php' => $quoteService,
    'QuoteNumberRepository.php' => $quoteNumberRepository,
] as $label => $contents) {
    foreach ($forbiddenFragments as $fragment) {
        if (str_contains($contents, $fragment)) {
            outputError("{$label} contiene un fragmento fuera de alcance: {$fragment}");
            exit(1);
        }
    }
}

outputOk('El contrato del nucleo de emision esta completo.');
outputOk('No existe endpoint publico de emision.');
outputOk('No se uso base de datos ni se emitio una cotizacion real.');
exit(0);

function readFileOrFail(string $path, string $label): string
{
    if (!is_file($path)) {
        outputError("No existe {$label}.");
        exit(1);
    }

    $contents = file_get_contents($path);

    if (!is_string($contents) || $contents === '') {
        outputError("No fue posible leer {$label}.");
        exit(1);
    }

    return $contents;
}

function assertContains(string $contents, string $fragment, string $message): void
{
    if (!str_contains($contents, $fragment)) {
        outputError($message);
        exit(1);
    }
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
