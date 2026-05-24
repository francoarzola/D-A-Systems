<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$endpointPath = __DIR__ . '/../public/cotizaciones-guardar.php';

if (!is_file($endpointPath)) {
    outputError('No existe sistema/public/cotizaciones-guardar.php.');
    exit(1);
}

$contents = file_get_contents($endpointPath);

if (!is_string($contents) || $contents === '') {
    outputError('No fue posible leer el endpoint de guardado.');
    exit(1);
}

$requiredFragments = [
    'CsrfToken',
    'FlashMessage',
    'QuoteService',
    'QuoteRepository',
    'QuoteDraftValidator',
    'QuoteTotalsCalculator',
    'DatabaseConfig',
    'Connection',
    'requireAuth',
    'quote_draft',
    '$_POST',
    "header('Location:",
    'REQUEST_METHOD',
    'POST',
    'cotizacion-detalle.php?id=',
    'cotizaciones.php',
    "header('Location: ' . \$path, true, 303)",
];

foreach ($requiredFragments as $fragment) {
    if (!str_contains($contents, $fragment)) {
        outputError("Falta referencia esperada en el endpoint: {$fragment}");
        exit(1);
    }
}

$forbiddenFragments = [
    'subtotal_neto',
    'descuento_monto',
    'iva_porcentaje',
    'iva_monto',
    'total',
    'numero_cotizacion',
    'estado',
    'cotizacion_correlativos',
];

foreach ($forbiddenFragments as $fragment) {
    if (str_contains($contents, "scalarFromArray(\$post, '{$fragment}')")
        || str_contains($contents, "scalarFromArray(\$post, \"{$fragment}\")")) {
        outputError("El endpoint parece aceptar un campo no permitido desde POST: {$fragment}");
        exit(1);
    }
}

outputOk('El contrato del endpoint cotizaciones-guardar.php está completo.');
outputOk('No se ejecutó POST real ni se usó base de datos.');
exit(0);

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
