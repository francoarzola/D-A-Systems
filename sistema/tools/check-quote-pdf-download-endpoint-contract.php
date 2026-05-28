<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$endpointPath = $rootPath . '/public/cotizacion-pdf.php';
$detailViewPath = $rootPath . '/public/cotizacion-detalle.php';
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';
$forbiddenDirectories = [
    $rootPath . '/public/pdfs',
    $rootPath . '/public/pdf',
    $rootPath . '/storage/pdfs',
];

$errors = [];

$endpoint = readFileContents($endpointPath, $errors);

assertContains($endpoint, [
    'SessionManager',
    'AuthGuard',
    'requireAuth',
    '$_GET',
    'QuoteService',
    'getQuoteDetail',
    'QuotePdfService',
    'assertCanGeneratePdf',
    'buildPdfFilename',
    'QuotePdfHtmlBuilder',
    'QuotePdfRenderService',
    'CompanyProfile',
    'Content-Type: application/pdf',
    'Content-Disposition',
    'Content-Length',
    'echo $pdf',
    'exit',
], 'cotizacion-pdf.php', $errors);

assertDoesNotContain($endpoint, [
    '$_POST',
    'INSERT',
    'UPDATE',
    'DELETE',
    'file_put_contents',
    'fopen',
    'readfile',
    'stream(',
    'mail(',
    'fetch(',
    'XMLHttpRequest',
    'application/json',
], 'cotizacion-pdf.php', $errors);

$detailView = readFileContents($detailViewPath, $errors);
$printView = readFileContents($printViewPath, $errors);
assertDoesNotContain($detailView . "\n" . $printView, [
    'Descargar PDF',
    'cotizacion-pdf.php',
], 'vistas de cotización', $errors);

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

echo "[OK] Endpoint de descarga PDF autenticado cumple el contrato.\n";
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
