<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$printPath = __DIR__ . '/../public/cotizacion-imprimir.php';
$detailPath = __DIR__ . '/../public/cotizacion-detalle.php';
$servicePath = __DIR__ . '/../app/Services/QuoteService.php';
$cssPath = __DIR__ . '/../public/assets/css/internal.css';

$print = readFileOrFail($printPath, 'cotizacion-imprimir.php');
$detail = readFileOrFail($detailPath, 'cotizacion-detalle.php');
$service = readFileOrFail($servicePath, 'QuoteService.php');
$css = readFileOrFail($cssPath, 'internal.css');

$printFragments = [
    'AuthGuard',
    'requireAuth',
    '$_GET',
    'getQuoteDetail',
    'ViewFormatter',
    'numero_cotizacion',
    'emitida',
    'window.print',
    'Volver al detalle',
];

foreach ($printFragments as $fragment) {
    assertContains($print, $fragment, "cotizacion-imprimir.php no contiene {$fragment}");
}

$detailFragments = [
    'cotizacion-imprimir.php',
    'Vista imprimible',
    'emitida',
    'numero_cotizacion',
];

foreach ($detailFragments as $fragment) {
    assertContains($detail, $fragment, "cotizacion-detalle.php no contiene {$fragment}");
}

assertContains($service, 'getQuoteDetail', 'QuoteService.php debe mantener getQuoteDetail.');
assertContains($css, '@media print', 'internal.css debe contener estilos @media print.');
assertContains($css, '.print-actions', 'internal.css debe ocultar o controlar acciones de impresión.');

$forbiddenFragments = [
    '$_POST',
    'INSERT',
    'UPDATE',
    'DELETE',
    'AJAX',
    'api json',
    'application/json',
    'mail(',
    'PDF',
];

foreach ([
    'cotizacion-imprimir.php' => $print,
    'cotizacion-detalle.php' => $detail,
] as $label => $contents) {
    foreach ($forbiddenFragments as $fragment) {
        if (stripos($contents, $fragment) !== false) {
            outputError("{$label} contiene un fragmento fuera de alcance: {$fragment}");
            exit(1);
        }
    }
}

$unexpectedFiles = [
    __DIR__ . '/../public/cotizacion-enviar.php',
    __DIR__ . '/../public/cotizacion-pdf.php',
    __DIR__ . '/../public/cotizacion-generar-pdf.php',
];

foreach ($unexpectedFiles as $path) {
    if (file_exists($path)) {
        outputError('No debe existir endpoint nuevo de correo o PDF en esta etapa.');
        exit(1);
    }
}

outputOk('El contrato de vista imprimible de cotización está completo.');
outputOk('No se usó base de datos, no se ejecutó POST y no se modificaron archivos.');
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
