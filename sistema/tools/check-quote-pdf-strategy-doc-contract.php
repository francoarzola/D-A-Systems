<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$projectRoot = dirname($rootPath);
$docPath = $projectRoot . '/docs/sistema-interno/77-evaluacion-estrategia-pdf-cotizaciones.md';
$pdfEndpointPath = $rootPath . '/public/cotizacion-pdf.php';
$detailViewPath = $rootPath . '/public/cotizacion-detalle.php';
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';

$errors = [];
$document = readFileContents($docPath, $errors);

assertContains($document, [
    'Dompdf',
    'mPDF',
    'wkhtmltopdf',
    'impresión desde navegador',
    'cPanel',
    'Composer',
    'QuotePdfService',
    'cotizacion-pdf.php',
    'cotizacion-imprimir.php',
    'Estrategia recomendada',
    'Qué NO se implementó',
], '77-evaluacion-estrategia-pdf-cotizaciones.md', $errors);

assertPathDoesNotExist($projectRoot . '/composer.json', 'composer.json', $errors);
assertPathDoesNotExist($projectRoot . '/composer.lock', 'composer.lock', $errors);
assertPathDoesNotExist($projectRoot . '/vendor', 'vendor/', $errors);

if (is_file($pdfEndpointPath)) {
    $endpoint = readFileContents($pdfEndpointPath, $errors);
    assertDoesNotContain($endpoint, [
        'Content-Type: application/pdf',
        'Content-Disposition',
        'readfile',
        'Dompdf',
        'mPDF',
        'wkhtmltopdf',
    ], 'cotizacion-pdf.php', $errors);
}

$detailView = readFileContents($detailViewPath, $errors);
$printView = readFileContents($printViewPath, $errors);
assertDoesNotContain($detailView . "\n" . $printView, [
    'Descargar PDF',
], 'vistas públicas', $errors);

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] La evaluación técnica de estrategia PDF cumple el contrato esperado.\n";
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

function assertDoesNotContain(string $contents, array $forbiddenNeedles, string $label, array &$errors): void
{
    foreach ($forbiddenNeedles as $needle) {
        if (str_contains($contents, $needle)) {
            $errors[] = $label . ' contiene fragmento no permitido: ' . $needle . '.';
        }
    }
}

function assertPathDoesNotExist(string $path, string $label, array &$errors): void
{
    if (file_exists($path)) {
        $errors[] = 'No debe existir ' . $label . ' en esta etapa.';
    }
}

function displayPath(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    $marker = '/D-A-Systems-audit/';
    $position = strpos($normalized, $marker);

    if ($position === false) {
        return $path;
    }

    return substr($normalized, $position + strlen($marker));
}
