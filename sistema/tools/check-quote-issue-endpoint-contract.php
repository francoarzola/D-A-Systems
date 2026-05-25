<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$basePath = dirname(__DIR__);
$issueEndpointPath = $basePath . '/public/cotizacion-emitir.php';
$detailPath = $basePath . '/public/cotizacion-detalle.php';
$servicePath = $basePath . '/app/Services/QuoteService.php';
$repositoryPath = $basePath . '/app/Repositories/QuoteRepository.php';

$issueEndpoint = readFileOrFail($issueEndpointPath, 'cotizacion-emitir.php');
$detail = readFileOrFail($detailPath, 'cotizacion-detalle.php');
$service = readFileOrFail($servicePath, 'QuoteService.php');
$repository = readFileOrFail($repositoryPath, 'QuoteRepository.php');

$endpointFragments = [
    'AuthGuard',
    'requireAuth',
    'CsrfToken',
    'FlashMessage',
    'QuoteService',
    'issueDraft',
    'quote_issue',
    '$_POST',
    "header('Location:",
    '303',
];

foreach ($endpointFragments as $fragment) {
    assertContains($issueEndpoint, $fragment, "cotizacion-emitir.php no contiene {$fragment}");
}

$detailFragments = [
    'cotizacion-emitir.php',
    'quote_issue',
    'method="post"',
    'name="cotizacion_id"',
    'Emitir cotizaci',
    'borrador',
];

foreach ($detailFragments as $fragment) {
    assertContains($detail, $fragment, "cotizacion-detalle.php no contiene {$fragment}");
}

assertNoMojibake($issueEndpoint, 'cotizacion-emitir.php');
assertNoMojibake($detail, 'cotizacion-detalle.php');

assertContains($service, 'issueDraft', 'QuoteService.php no contiene issueDraft');

$repositoryFragments = [
    'issueDraft',
    'reserveNextNumberInCurrentTransaction',
    'emitida',
];

foreach ($repositoryFragments as $fragment) {
    assertContains($repository, $fragment, "QuoteRepository.php no contiene {$fragment}");
}

$forbiddenPostFields = [
    'numero_cotizacion',
    'estado',
    'subtotal_neto',
    'iva_porcentaje',
    'iva_monto',
    'total',
];

foreach ($forbiddenPostFields as $field) {
    if (hasPostRead($issueEndpoint, $field)) {
        outputError("cotizacion-emitir.php lee {$field} desde POST.");
        exit(1);
    }
}

$forbiddenFragments = [
    'AJAX',
    'API JSON',
    'pdf',
    'mail(',
    'enviarCorreo',
];

foreach ([
    'cotizacion-emitir.php' => $issueEndpoint,
    'cotizacion-detalle.php' => $detail,
] as $label => $contents) {
    foreach ($forbiddenFragments as $fragment) {
        if (stripos($contents, $fragment) !== false) {
            outputError("{$label} contiene un fragmento fuera de alcance: {$fragment}");
            exit(1);
        }
    }
}

outputOk('El contrato del endpoint de emision esta completo.');
outputOk('No se detectaron campos prohibidos leidos desde POST.');
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

function hasPostRead(string $contents, string $field): bool
{
    $singleQuote = '$_POST[\'' . $field . '\']';
    $doubleQuote = '$_POST["' . $field . '"]';

    return str_contains($contents, $singleQuote) || str_contains($contents, $doubleQuote);
}

function assertNoMojibake(string $contents, string $label): void
{
    foreach (['Ã', 'Â'] as $fragment) {
        if (str_contains($contents, $fragment)) {
            outputError("{$label} contiene texto con codificacion incorrecta: {$fragment}");
            exit(1);
        }
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
