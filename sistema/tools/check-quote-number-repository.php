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
    'public function reserveNextNumberInCurrentTransaction(string $documentType, int $year): string',
    '$this->reserveNextNumberInCurrentTransaction($documentType, $year)',
    'beginTransaction',
    'commit',
    'rollBack',
    'cotizacion_correlativos',
    'tipo_documento',
    'anio',
    'ultimo_numero',
    'FOR UPDATE',
    'INSERT INTO cotizacion_correlativos',
    'ON DUPLICATE KEY UPDATE',
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
    'FROM cotizaciones',
    'MAX(',
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

assertMethodDoesNotContainTransactionControl(
    $repository,
    'reserveNextNumberInCurrentTransaction',
    [
        'beginTransaction',
        'commit',
        'rollBack',
    ]
);

outputOk('El contrato de QuoteNumberRepository está completo.');
outputOk('No se reservó número real, no se ejecutó SQL y no se usó base de datos.');
outputOk('reserveNextNumberInCurrentTransaction reutiliza la transaccion abierta por el caller.');
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

function assertMethodDoesNotContainTransactionControl(string $contents, string $methodName, array $fragments): void
{
    $method = extractMethodBody($contents, $methodName);

    foreach ($fragments as $fragment) {
        if (str_contains($method, $fragment)) {
            outputError("{$methodName} contiene control de transaccion fuera de alcance: {$fragment}");
            exit(1);
        }
    }
}

function extractMethodBody(string $contents, string $methodName): string
{
    $needle = 'function ' . $methodName . '(';
    $start = strpos($contents, $needle);

    if ($start === false) {
        outputError("No existe el metodo {$methodName}.");
        exit(1);
    }

    $openBrace = strpos($contents, '{', $start);

    if ($openBrace === false) {
        outputError("No fue posible leer el metodo {$methodName}.");
        exit(1);
    }

    $depth = 0;
    $length = strlen($contents);

    for ($index = $openBrace; $index < $length; $index++) {
        if ($contents[$index] === '{') {
            $depth++;
        }

        if ($contents[$index] === '}') {
            $depth--;

            if ($depth === 0) {
                return substr($contents, $openBrace, $index - $openBrace + 1);
            }
        }
    }

    outputError("No fue posible delimitar el metodo {$methodName}.");
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
