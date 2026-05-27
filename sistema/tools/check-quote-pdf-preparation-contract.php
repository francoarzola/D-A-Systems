<?php

declare(strict_types=1);

use DAndASystems\Internal\Services\QuotePdfService;

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$servicePath = $rootPath . '/app/Services/QuotePdfService.php';
$publicEndpointPath = $rootPath . '/public/cotizacion-pdf.php';
$detailViewPath = $rootPath . '/public/cotizacion-detalle.php';
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';

$errors = [];
$serviceContents = readFileContents($servicePath, $errors);
$detailView = readFileContents($detailViewPath, $errors);
$printView = readFileContents($printViewPath, $errors);

assertContains($serviceContents, [
    'QuotePdfService',
    'canGeneratePdf',
    'assertCanGeneratePdf',
    'buildPdfFilename',
    'numero_cotizacion',
    'emitida',
    '.pdf',
], 'QuotePdfService.php', $errors);

assertDoesNotContain($serviceContents, [
    '$_GET',
    '$_POST',
    'INSERT',
    'UPDATE',
    'DELETE',
    'PDO',
    'new Connection',
    'Dompdf',
    'composer',
    'header(',
    'application/pdf',
    'file_put_contents',
    'fopen',
    'AJAX',
    'fetch(',
    'XMLHttpRequest',
    'application/json',
], $errors);

if (preg_match('/(?<![A-Za-z0-9_])mail\s*\(/', $serviceContents) === 1) {
    $errors[] = 'Se encontró llamada no permitida a mail().';
}

if (is_file($publicEndpointPath)) {
    $errors[] = 'No debe existir sistema/public/cotizacion-pdf.php en esta etapa.';
}

if (str_contains($detailView . "\n" . $printView, 'Descargar PDF')) {
    $errors[] = 'No debe agregarse texto "Descargar PDF" en las vistas públicas.';
}

if ($serviceContents !== '') {
    require_once $servicePath;
    runServiceChecks($errors);
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] La preparación interna de PDF cumple el contrato esperado.\n";
exit(0);

function runServiceChecks(array &$errors): void
{
    $service = new QuotePdfService();
    $issuedQuote = [
        'estado' => 'emitida',
        'numero_cotizacion' => 'COT-2026-0001',
    ];
    $draftQuote = [
        'estado' => 'borrador',
        'numero_cotizacion' => null,
    ];
    $issuedWithoutNumber = [
        'estado' => 'emitida',
        'numero_cotizacion' => '',
    ];

    if (!$service->canGeneratePdf($issuedQuote)) {
        $errors[] = 'Una cotización emitida con número oficial debería permitir preparación PDF.';
    }

    if ($service->canGeneratePdf($draftQuote)) {
        $errors[] = 'Un borrador no debería permitir preparación PDF.';
    }

    if ($service->canGeneratePdf($issuedWithoutNumber)) {
        $errors[] = 'Una cotización emitida sin número oficial no debería permitir preparación PDF.';
    }

    if ($service->buildPdfFilename($issuedQuote) !== 'COT-2026-0001.pdf') {
        $errors[] = 'buildPdfFilename no retornó COT-2026-0001.pdf.';
    }
}

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
