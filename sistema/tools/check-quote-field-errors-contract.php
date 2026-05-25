<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$pagePath = __DIR__ . '/../public/cotizaciones.php';
$endpointPath = __DIR__ . '/../public/cotizaciones-guardar.php';
$cssPath = __DIR__ . '/../public/assets/css/internal.css';

$page = readFileOrFail($pagePath, 'cotizaciones.php');
$endpoint = readFileOrFail($endpointPath, 'cotizaciones-guardar.php');
$css = readFileOrFail($cssPath, 'internal.css');

$endpointFragments = [
    'quote_draft_errors',
    "set('quote_draft_errors'",
    "clear('quote_draft_errors'",
];

foreach ($endpointFragments as $fragment) {
    assertContains($endpoint, $fragment, "cotizaciones-guardar.php no contiene {$fragment}");
}

if (substr_count($endpoint, "clear('quote_draft_errors'") < 4) {
    outputError('cotizaciones-guardar.php debe limpiar quote_draft_errors en solicitudes inválidas, CSRF inválido, guardado correcto y error técnico.');
    exit(1);
}

$pageFragments = [
    "pull('quote_draft_errors'",
    'fieldError',
    'detailFieldError',
    'field-error',
    'form-error-summary',
    'ViewFormatter::e',
];

foreach ($pageFragments as $fragment) {
    assertContains($page, $fragment, "cotizaciones.php no contiene {$fragment}");
}

$cssFragments = [
    '.field-error',
    '.field-has-error',
    '.form-error-summary',
];

foreach ($cssFragments as $fragment) {
    assertContains($css, $fragment, "internal.css no contiene {$fragment}");
}

$forbiddenInputNames = [
    'numero_cotizacion',
    'estado',
    'subtotal_neto',
    'iva_monto',
    'total',
];

foreach ($forbiddenInputNames as $name) {
    if (str_contains($page, 'name="' . $name . '"')
        || str_contains($page, "name='{$name}'")) {
        outputError("El formulario contiene un campo no permitido: {$name}");
        exit(1);
    }
}

outputOk('El contrato de errores por campo está completo.');
outputOk('No se ejecutó POST real ni se usó base de datos.');
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
