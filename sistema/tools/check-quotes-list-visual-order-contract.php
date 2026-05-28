<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$quotesPath = $root . '/sistema/public/cotizaciones.php';
$cssPath = $root . '/sistema/public/assets/css/internal.css';

$requiredFiles = [
    $quotesPath,
    $cssPath,
    $root . '/sistema/public/cotizacion-detalle.php',
    $root . '/sistema/public/cotizacion-editar.php',
    $root . '/sistema/public/cotizacion-pdf.php',
];

foreach ($requiredFiles as $path) {
    if (!is_file($path)) {
        fail('No existe el archivo requerido: ' . relativePath($root, $path));
    }
}

$quotesContent = file_get_contents($quotesPath);
$cssContent = file_get_contents($cssPath);

if ($quotesContent === false || $cssContent === false) {
    fail('No fue posible leer los archivos requeridos.');
}

$requiredQuoteFragments = [
    'internal-topbar',
    'Cotizaciones',
    'Crear borrador',
    'inputField',
    'method="post"',
    'type="submit"',
    'cotizacion-detalle.php?id=',
    'cotizacion-editar.php?id=',
    'ViewFormatter::e',
    '$quotes',
    'foreach',
    'estado',
    'numero_cotizacion',
    'nombre_cliente',
    'total',
];

foreach ($requiredQuoteFragments as $fragment) {
    assertContains($quotesContent, $fragment, 'cotizaciones.php debe contener: ' . $fragment);
}

$forbiddenFragments = [
    'fetch(',
    'XMLHttpRequest',
    'application/json',
    'file_put_contents',
    'readfile',
    'stream(',
];

foreach ($forbiddenFragments as $fragment) {
    assertNotContains($quotesContent, $fragment, 'cotizaciones.php no debe contener: ' . $fragment);
}

if (preg_match('/(?<![A-Za-z0-9_>:-])mail\s*\(/', $quotesContent) === 1) {
    fail('cotizaciones.php no debe introducir envio de correo.');
}

$requiredCssFragments = [
    'quotes-page-heading',
    'quotes-list-section',
    'quote-create-section',
    'quotes-section-header',
];

foreach ($requiredCssFragments as $fragment) {
    assertContains($cssContent, $fragment, 'internal.css debe contener clase de orden visual: ' . $fragment);
    assertContains($quotesContent, $fragment, 'cotizaciones.php debe usar clase de orden visual: ' . $fragment);
}

$cssFiles = glob($root . '/sistema/public/assets/css/*.css') ?: [];
foreach ($cssFiles as $cssFile) {
    if (basename($cssFile) !== 'internal.css') {
        fail('No se deben crear archivos CSS nuevos fuera de internal.css: ' . relativePath($root, $cssFile));
    }
}

$jsFiles = glob($root . '/sistema/public/assets/js/*.js') ?: [];
if ($jsFiles !== []) {
    fail('No se deben crear archivos JS nuevos en assets/js.');
}

echo "[OK] Orden visual del listado de cotizaciones cumple el contrato esperado.\n";

function assertContains(string $content, string $needle, string $message): void
{
    if (strpos($content, $needle) === false) {
        fail($message);
    }
}

function assertNotContains(string $content, string $needle, string $message): void
{
    if (strpos($content, $needle) !== false) {
        fail($message);
    }
}

function relativePath(string $root, string $path): string
{
    $normalizedRoot = str_replace('\\', '/', rtrim($root, '/\\'));
    $normalizedPath = str_replace('\\', '/', $path);

    if (str_starts_with($normalizedPath, $normalizedRoot . '/')) {
        return substr($normalizedPath, strlen($normalizedRoot) + 1);
    }

    return $path;
}

function fail(string $message): void
{
    echo '[ERROR] ' . $message . "\n";
    exit(1);
}
