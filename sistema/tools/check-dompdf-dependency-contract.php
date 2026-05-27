<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$projectRoot = dirname($rootPath);
$composerPath = $projectRoot . '/composer.json';
$autoloadPath = $projectRoot . '/vendor/autoload.php';
$pdfEndpointPath = $rootPath . '/public/cotizacion-pdf.php';
$detailViewPath = $rootPath . '/public/cotizacion-detalle.php';
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';

$errors = [];

if (!is_file($composerPath)) {
    $errors[] = 'No existe composer.json.';
} else {
    $composerJson = readFileContents($composerPath, $errors);
    $composerData = json_decode($composerJson, true);

    if (!is_array($composerData)) {
        $errors[] = 'composer.json no contiene JSON válido.';
    }

    if (!str_contains($composerJson, 'dompdf/dompdf')) {
        $errors[] = 'composer.json no contiene dompdf/dompdf.';
    }

    assertDoesNotContain($composerJson, [
        'mpdf/mpdf',
        'wkhtmltopdf',
    ], 'composer.json', $errors);
}

$pdfEndpoint = readFileContents($pdfEndpointPath, $errors);
assertDoesNotContain($pdfEndpoint, [
    'Content-Type: application/pdf',
    'Content-Disposition',
    'readfile',
    'output()',
    'stream()',
], 'cotizacion-pdf.php', $errors);

$detailView = readFileContents($detailViewPath, $errors);
$printView = readFileContents($printViewPath, $errors);
assertDoesNotContain($detailView . "\n" . $printView, [
    'Descargar PDF',
], 'vistas públicas', $errors);

if (!is_file($autoloadPath)) {
    $errors[] = 'vendor/autoload.php no existe. Ejecutar composer install.';
} else {
    require_once $autoloadPath;

    if (!class_exists(\Dompdf\Dompdf::class)) {
        $errors[] = 'Dompdf\\Dompdf no está disponible desde vendor/autoload.php.';
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] Dompdf disponible y dependencia preparada sin activar PDF real.\n";
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
    $marker = '/D-A-Systems-audit/';
    $position = strpos($normalized, $marker);

    if ($position === false) {
        return $path;
    }

    return substr($normalized, $position + strlen($marker));
}
