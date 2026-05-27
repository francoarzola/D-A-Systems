<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';
$cssPath = $rootPath . '/public/assets/css/internal.css';

$errors = [];
$printView = readFileContents($printViewPath, $errors);
$css = readFileContents($cssPath, $errors);

assertContains($printView, [
    'AuthGuard',
    'requireAuth',
    'CompanyProfile',
    'ViewFormatter::e',
    'getQuoteDetail',
    'isPrintableQuote',
    'window.print',
    'Volver al detalle',
    'numero_cotizacion',
    'emitida',
], 'cotizacion-imprimir.php', $errors);

assertContains($css, [
    '@media print',
    '@page',
    'print-document',
    'print-header',
    'print-table',
    'print-summary',
    'print-footer',
    'print-actions',
], 'internal.css', $errors);

if (!preg_match('/\.print-actions\s*\{[^}]*display\s*:\s*none\s*;/s', $css)) {
    $errors[] = 'internal.css no oculta .print-actions en impresión.';
}

assertDoesNotContain($printView . "\n" . $css, [
    '$_POST',
    'INSERT',
    'UPDATE',
    'DELETE',
    'PDF',
    'ajax',
    'fetch(',
    'XMLHttpRequest',
    'application/json',
], $errors);

if (preg_match('/(?<![A-Za-z0-9_])mail\s*\(/', $printView . "\n" . $css) === 1) {
    $errors[] = 'Se encontró llamada no permitida a mail().';
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] La vista imprimible mantiene el contrato visual A4 esperado.\n";
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
