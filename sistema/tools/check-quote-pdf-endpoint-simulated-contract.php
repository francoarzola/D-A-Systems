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
$pdfServicePath = $rootPath . '/app/Services/QuotePdfService.php';

$errors = [];
$endpoint = readFileContents($endpointPath, $errors);
$detailView = readFileContents($detailViewPath, $errors);
$printView = readFileContents($printViewPath, $errors);
$pdfService = readFileContents($pdfServicePath, $errors);

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
    'ViewFormatter::e',
    'http_response_code(501)',
    'Volver al detalle',
], 'cotizacion-pdf.php', $errors);

assertDoesNotContain($endpoint, [
    '$_POST',
    'INSERT',
    'UPDATE',
    'DELETE',
    'Content-Type: application/pdf',
    'Content-Disposition',
    'readfile',
    'file_put_contents',
    'fopen',
    'Dompdf',
    'composer',
    'fetch(',
    'XMLHttpRequest',
    'application/json',
], $errors);

if (preg_match('/(?<![A-Za-z0-9_])mail\s*\(/', $endpoint) === 1) {
    $errors[] = 'cotizacion-pdf.php contiene llamada no permitida a mail().';
}

assertDoesNotContain($detailView . "\n" . $printView, [
    'Descargar PDF',
    'cotizacion-pdf.php',
], $errors);

assertContains($pdfService, [
    'canGeneratePdf',
    'assertCanGeneratePdf',
    'buildPdfFilename',
], 'QuotePdfService.php', $errors);

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] El endpoint PDF simulado cumple el contrato esperado.\n";
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

function assertContains(string $contents, array $needles, string $label, array &$errors): void
{
    foreach ($needles as $needle) {
        if (!str_contains($contents, $needle)) {
            $errors[] = $label . ' no contiene referencia requerida: ' . $needle . '.';
        }
    }
}

function assertDoesNotContain(string $contents, array $forbiddenNeedles, array &$errors): void
{
    foreach ($forbiddenNeedles as $needle) {
        if (str_contains($contents, $needle)) {
            $errors[] = 'Se encontró fragmento no permitido: ' . $needle . '.';
        }
    }
}

function displayPath(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    $marker = '/sistema/';
    $position = strpos($normalized, $marker);

    if ($position === false) {
        return $path;
    }

    return substr($normalized, $position + 1);
}
