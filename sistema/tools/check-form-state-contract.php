<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$formStatePath = __DIR__ . '/../app/Support/FormState.php';
$pagePath = __DIR__ . '/../public/cotizaciones.php';
$endpointPath = __DIR__ . '/../public/cotizaciones-guardar.php';

$formState = readFileOrFail($formStatePath, 'FormState.php');
$page = readFileOrFail($pagePath, 'cotizaciones.php');
$endpoint = readFileOrFail($endpointPath, 'cotizaciones-guardar.php');

$formStateFragments = [
    'class FormState',
    'set(',
    'get(',
    'pull(',
    'clear(',
    '$_SESSION',
];

foreach ($formStateFragments as $fragment) {
    assertContains($formState, $fragment, "FormState.php no contiene {$fragment}");
}

$endpointFragments = [
    'FormState',
    'quote_draft',
    "set('quote_draft'",
    "clear('quote_draft'",
];

foreach ($endpointFragments as $fragment) {
    assertContains($endpoint, $fragment, "cotizaciones-guardar.php no contiene {$fragment}");
}

$pageFragments = [
    'FormState',
    "pull('quote_draft'",
    'ViewFormatter::e',
    'formValue($draftState, \'nombre_cliente\'',
    'formValue($draftState, \'fecha_cotizacion\'',
    'formValue($draftFirstDetail, \'descripcion\'',
    'formValue($draftFirstDetail, \'cantidad\'',
];

foreach ($pageFragments as $fragment) {
    assertContains($page, $fragment, "cotizaciones.php no contiene {$fragment}");
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

outputOk('El contrato de persistencia temporal del formulario está completo.');
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
