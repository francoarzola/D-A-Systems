<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$repositoryPath = __DIR__ . '/../app/Repositories/QuoteNumberRepository.php';
$repository = readFileOrFail($repositoryPath, 'QuoteNumberRepository.php');

$requiredFragments = [
    'final class QuoteNumberRepository',
    'public function __construct(PDO $pdo)',
    'public function reserveNextNumber(string $documentType, int $year): string',
    'beginTransaction',
    'commit',
    'rollBack',
    'cotizacion_correlativos',
    'tipo_documento',
    'anio',
    'ultimo_numero',
    'FOR UPDATE',
    'INSERT INTO cotizacion_correlativos',
    'UPDATE cotizacion_correlativos',
    "sprintf('%s-%d-%04d'",
];

foreach ($requiredFragments as $fragment) {
    assertContains($repository, $fragment, "QuoteNumberRepository.php no contiene {$fragment}");
}

$forbiddenFragments = [
    'UPDATE cotizaciones',
    'INSERT INTO cotizaciones',
    'DELETE FROM cotizaciones',
    "estado = 'emitida'",
    'quote_draft',
    '$_POST',
    '$_GET',
];

foreach ($forbiddenFragments as $fragment) {
    if (str_contains($repository, $fragment)) {
        outputError("QuoteNumberRepository.php contiene un fragmento fuera de alcance: {$fragment}");
        exit(1);
    }
}

outputOk('El contrato de QuoteNumberRepository está completo.');
outputOk('No se reservó número real, no se ejecutó SQL y no se usó base de datos.');
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
