<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$detailViewPath = $rootPath . '/public/cotizacion-detalle.php';
$pdfEndpointPath = $rootPath . '/public/cotizacion-pdf.php';
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';
$quotesViewPath = $rootPath . '/public/cotizaciones.php';
$forbiddenDirectories = [
    $rootPath . '/public/pdfs',
    $rootPath . '/public/pdf',
    $rootPath . '/storage/pdfs',
];

$errors = [];

$detailView = readFileContents($detailViewPath, $errors);
assertContains($detailView, [
    'Descargar PDF',
    'cotizacion-pdf.php?id=',
    'numero_cotizacion',
    'emitida',
    'ViewFormatter::e',
], 'cotizacion-detalle.php', $errors);

assertContainsNear($detailView, "=== 'emitida'", 'cotizacion-pdf.php?id=', 'cotizacion-detalle.php', $errors);
assertContainsNear($detailView, 'numero_cotizacion', 'cotizacion-pdf.php?id=', 'cotizacion-detalle.php', $errors);
assertContainsNear($detailView, "!== ''", 'cotizacion-pdf.php?id=', 'cotizacion-detalle.php', $errors);

$pdfEndpoint = readFileContents($pdfEndpointPath, $errors);
assertContains($pdfEndpoint, [
    'Content-Type: application/pdf',
    'Content-Disposition',
    'QuotePdfHtmlBuilder',
    'QuotePdfRenderService',
    'assertCanGeneratePdf',
], 'cotizacion-pdf.php', $errors);

$printView = readFileContents($printViewPath, $errors);
assertDoesNotContain($printView, [
    'Descargar PDF',
    'cotizacion-pdf.php',
], 'cotizacion-imprimir.php', $errors);

$quotesView = readFileContents($quotesViewPath, $errors);
assertDoesNotContain($quotesView, [
    'Descargar PDF',
    'cotizacion-pdf.php',
], 'cotizaciones.php', $errors);

foreach ($forbiddenDirectories as $directory) {
    if (is_dir($directory)) {
        $errors[] = 'Existe carpeta de PDF no permitida: ' . displayPath($directory) . '.';
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] Botón Descargar PDF condicionado para cotizaciones emitidas con número oficial.\n";
exit(0);

function readFileContents(string $path, array &$errors): string
{
    if (!is_file($path)) {
        $errors[] = 'No existe ' . displayPath($path) . '.';

        return '';
    }

    $contents = file_get_contents($path);

    if (!is_string($contents)) {
        $errors[] = 'No fue posible leer ' . displayPath($path) . '.';

        return '';
    }

    return $contents;
}

function assertContains(string $contents, array $requiredNeedles, string $label, array &$errors): void
{
    foreach ($requiredNeedles as $needle) {
        if (!str_contains($contents, $needle)) {
            $errors[] = $label . ' no contiene fragmento esperado: ' . $needle . '.';
        }
    }
}

function assertDoesNotContain(string $contents, array $forbiddenNeedles, string $label, array &$errors): void
{
    foreach ($forbiddenNeedles as $needle) {
        if (str_contains($contents, $needle)) {
            $errors[] = $label . ' contiene fragmento no permitido: ' . $needle . '.';
        }
    }
}

function assertContainsNear(string $contents, string $guardNeedle, string $targetNeedle, string $label, array &$errors): void
{
    $targetPosition = strpos($contents, $targetNeedle);

    if ($targetPosition === false) {
        return;
    }

    $start = max(0, $targetPosition - 500);
    $snippet = substr($contents, $start, 1000);

    if (!str_contains($snippet, $guardNeedle)) {
        $errors[] = $label . ' no condiciona Descargar PDF con: ' . $guardNeedle . '.';
    }
}

function displayPath(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    $marker = '/D-A-Systems/';
    $position = strpos($normalized, $marker);

    if ($position === false) {
        return $path;
    }

    return substr($normalized, $position + strlen($marker));
}
