<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$editPath = __DIR__ . '/../public/cotizacion-editar.php';
$listPath = __DIR__ . '/../public/cotizaciones.php';
$detailPath = __DIR__ . '/../public/cotizacion-detalle.php';
$futureUpdatePath = __DIR__ . '/../public/cotizacion-actualizar.php';

$edit = readFileOrFail($editPath, 'cotizacion-editar.php');
$list = readFileOrFail($listPath, 'cotizaciones.php');
$detail = readFileOrFail($detailPath, 'cotizacion-detalle.php');

$editFragments = [
    'AuthGuard',
    'requireAuth',
    'QuoteService',
    'QuoteRepository',
    'CsrfToken',
    'quote_draft_edit',
    '$_GET',
    'borrador',
    'ViewFormatter::e',
];

foreach ($editFragments as $fragment) {
    assertContains($edit, $fragment, "cotizacion-editar.php no contiene {$fragment}");
}

if (str_contains($edit, 'action="cotizacion-actualizar.php"')
    || str_contains($edit, "action='cotizacion-actualizar.php'")) {
    outputError('cotizacion-editar.php no debe apuntar a cotizacion-actualizar.php mientras la edición sea preparatoria.');
    exit(1);
}

if (str_contains($edit, 'method="post"')
    || str_contains($edit, "method='post'")) {
    outputError('cotizacion-editar.php no debe tener method="post" mientras la edición sea preparatoria.');
    exit(1);
}

$listFragments = [
    'cotizacion-editar.php?id=',
    'borrador',
];

foreach ($listFragments as $fragment) {
    assertContains($list, $fragment, "cotizaciones.php no contiene {$fragment}");
}

$detailFragments = [
    'cotizacion-editar.php?id=',
    'borrador',
];

foreach ($detailFragments as $fragment) {
    assertContains($detail, $fragment, "cotizacion-detalle.php no contiene {$fragment}");
}

if (file_exists($futureUpdatePath)) {
    outputError('No debe existir sistema/public/cotizacion-actualizar.php en esta etapa.');
    exit(1);
}

$writingFragments = [
    'UPDATE cotizaciones',
    'DELETE',
    'INSERT INTO cotizaciones',
];

foreach ([
    'cotizacion-editar.php' => $edit,
    'cotizaciones.php' => $list,
    'cotizacion-detalle.php' => $detail,
] as $label => $contents) {
    foreach ($writingFragments as $fragment) {
        if (stripos($contents, $fragment) !== false) {
            outputError("{$label} contiene una operación de escritura no permitida: {$fragment}");
            exit(1);
        }
    }
}

outputOk('El contrato de preparación de edición de borradores está completo.');
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
