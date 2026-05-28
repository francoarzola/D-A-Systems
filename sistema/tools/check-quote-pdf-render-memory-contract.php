<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$projectRoot = dirname($rootPath);
$autoloadPath = $projectRoot . '/vendor/autoload.php';
$renderServicePath = $rootPath . '/app/Services/QuotePdfRenderService.php';
$pdfEndpointPath = $rootPath . '/public/cotizacion-pdf.php';
$detailViewPath = $rootPath . '/public/cotizacion-detalle.php';
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';

$errors = [];

if (!is_file($autoloadPath)) {
    $errors[] = 'vendor/autoload.php no existe. Ejecutar composer install.';
} else {
    require_once $autoloadPath;
}

if (!is_file($renderServicePath)) {
    $errors[] = 'No existe sistema/app/Services/QuotePdfRenderService.php.';
} else {
    $renderServiceContents = readFileContents($renderServicePath, $errors);

    assertContains($renderServiceContents, [
        'QuotePdfRenderService',
        'renderHtmlToPdf',
        'Dompdf',
        'Options',
        'isRemoteEnabled',
        'isPhpEnabled',
        'setPaper',
        'A4',
        'portrait',
        'output()',
    ], 'QuotePdfRenderService.php', $errors);

    assertDoesNotContain($renderServiceContents, [
        '$_GET',
        '$_POST',
        'PDO',
        'file_put_contents',
        'fopen',
        'readfile',
        'stream(',
        'header(',
        'Content-Type: application/pdf',
        'Content-Disposition',
        'INSERT',
        'UPDATE',
        'DELETE',
    ], 'QuotePdfRenderService.php', $errors);

    require_once $renderServicePath;
}

$pdfEndpoint = readFileContents($pdfEndpointPath, $errors);
assertDoesNotContain($pdfEndpoint, [
    'Content-Type: application/pdf',
    'Content-Disposition',
    'stream(',
    'readfile',
], 'cotizacion-pdf.php', $errors);

$detailView = readFileContents($detailViewPath, $errors);
$printView = readFileContents($printViewPath, $errors);
assertDoesNotContain($detailView . "\n" . $printView, [
    'Descargar PDF',
    'cotizacion-pdf.php',
], 'vistas de cotización', $errors);

if ($errors === [] && !class_exists(\Dompdf\Dompdf::class)) {
    $errors[] = 'Dompdf\\Dompdf no está disponible. Ejecutar composer install.';
}

if ($errors === []) {
    try {
        $service = new \DAndASystems\Internal\Services\QuotePdfRenderService();
        $pdf = $service->renderHtmlToPdf(buildSampleHtml());

        if (!is_string($pdf)) {
            $errors[] = 'El resultado del render no es string.';
        }

        if ($pdf === '') {
            $errors[] = 'El resultado del render está vacío.';
        }

        if (!str_starts_with($pdf, '%PDF')) {
            $errors[] = 'El resultado del render no comienza con %PDF.';
        }

        if (strlen($pdf) <= 1000) {
            $errors[] = 'El PDF generado en memoria no supera 1000 bytes.';
        }
    } catch (\Throwable $exception) {
        $errors[] = 'No fue posible renderizar PDF en memoria.';
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] Prototipo de render PDF en memoria generado correctamente.\n";
echo "[OK] No se guardó archivo, no se envió descarga y no se usó base de datos.\n";
exit(0);

function buildSampleHtml(): string
{
    return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cotización de prueba</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
    h1 { font-size: 22px; margin-bottom: 4px; }
    .meta { color: #4b5563; margin-bottom: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 16px; }
    th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
    th { background: #f3f4f6; }
    .amount { text-align: right; }
    .total { font-size: 16px; font-weight: bold; margin-top: 20px; text-align: right; }
  </style>
</head>
<body>
  <h1>Cotización de prueba</h1>
  <p class="meta">Número COT-2026-0001</p>
  <p><strong>Cliente:</strong> Cliente de prueba D&amp;A Systems</p>
  <table>
    <thead>
      <tr>
        <th>Línea</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th class="amount">Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Servicio de validación PDF en memoria</td>
        <td>1</td>
        <td class="amount">$10.000</td>
      </tr>
    </tbody>
  </table>
  <p class="total">Total $11.900</p>
</body>
</html>
HTML;
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
