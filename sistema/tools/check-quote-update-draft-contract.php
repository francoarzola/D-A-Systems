<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$endpointPath = __DIR__ . '/../public/cotizacion-actualizar.php';
$editPath = __DIR__ . '/../public/cotizacion-editar.php';
$repositoryPath = __DIR__ . '/../app/Repositories/QuoteRepository.php';
$servicePath = __DIR__ . '/../app/Services/QuoteService.php';

$endpoint = readFileOrFail($endpointPath, 'cotizacion-actualizar.php');
$edit = readFileOrFail($editPath, 'cotizacion-editar.php');
$repository = readFileOrFail($repositoryPath, 'QuoteRepository.php');
$service = readFileOrFail($servicePath, 'QuoteService.php');

$endpointFragments = [
    'REQUEST_METHOD',
    'POST',
    'requireAuth',
    'CsrfToken',
    'quote_draft_edit',
    'cotizacion_id',
    'QuoteService',
    'updateDraft',
    'FlashMessage',
    'header(\'Location: \' . $path, true, 303)',
];

foreach ($endpointFragments as $fragment) {
    assertContains($endpoint, $fragment, "cotizacion-actualizar.php no contiene {$fragment}");
}

$editFragments = [
    'method="post"',
    'action="cotizacion-actualizar.php"',
    'quote_draft_edit',
    'name="cotizacion_id"',
    'Guardar cambios',
];

foreach ($editFragments as $fragment) {
    assertContains($edit, $fragment, "cotizacion-editar.php no contiene {$fragment}");
}

$repositoryFragments = [
    'function updateDraft',
    'beginTransaction',
    'FOR UPDATE',
    'estado',
    'borrador',
    'UPDATE cotizaciones',
    'DELETE FROM cotizacion_detalles',
    'insertDraftDetails',
    'commit',
    'rollBack',
];

foreach ($repositoryFragments as $fragment) {
    assertContains($repository, $fragment, "QuoteRepository.php no contiene {$fragment}");
}

$serviceFragments = [
    'function updateDraft',
    'validateDraft',
    'totalsCalculator',
    'updateDraft($quoteId',
];

foreach ($serviceFragments as $fragment) {
    assertContains($service, $fragment, "QuoteService.php no contiene {$fragment}");
}

$forbiddenEndpointFragments = [
    "scalarFromArray(\$post, 'numero_cotizacion')",
    "scalarFromArray(\$post, \"numero_cotizacion\")",
    "scalarFromArray(\$post, 'estado')",
    "scalarFromArray(\$post, \"estado\")",
    "scalarFromArray(\$post, 'subtotal_neto')",
    "scalarFromArray(\$post, \"subtotal_neto\")",
    "scalarFromArray(\$post, 'iva_monto')",
    "scalarFromArray(\$post, \"iva_monto\")",
    "scalarFromArray(\$post, 'total')",
    "scalarFromArray(\$post, \"total\")",
    'cotizacion_correlativos',
];

foreach ($forbiddenEndpointFragments as $fragment) {
    if (str_contains($endpoint, $fragment)) {
        outputError("cotizacion-actualizar.php contiene un fragmento no permitido: {$fragment}");
        exit(1);
    }
}

outputOk('El contrato de actualización de borradores está completo.');
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
