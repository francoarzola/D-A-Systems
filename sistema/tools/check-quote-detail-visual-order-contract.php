<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

$base = __DIR__ . '/..';
$detailPath = $base . '/public/cotizacion-detalle.php';
$cssPath = $base . '/public/assets/css/internal.css';

$requiredFiles = [
    $detailPath,
    $cssPath,
    $base . '/public/cotizaciones.php',
    $base . '/public/cotizacion-editar.php',
    $base . '/public/cotizacion-pdf.php',
    $base . '/public/cotizacion-imprimir.php',
    $base . '/public/cotizacion-emitir.php',
];

$missing = [];
foreach ($requiredFiles as $f) {
    if (!file_exists($f)) {
        $missing[] = $f;
    }
}

if ($missing !== []) {
    echo "[FAIL] Missing required files:\n" . implode("\n", $missing) . "\n";
    exit(2);
}

$detail = file_get_contents($detailPath);
$css = file_get_contents($cssPath);

$checks = [
    'internal-topbar' => strpos($detail, 'internal-topbar') !== false,
    'Detalle de cotización' => strpos($detail, 'Detalle de cotización') !== false,
    'numero_cotizacion' => strpos($detail, 'numero_cotizacion') !== false,
    'estado' => strpos($detail, "quote['estado'") !== false || strpos($detail, 'quote[\'estado\'') !== false,
    'nombre_cliente' => strpos($detail, 'nombre_cliente') !== false,
    'total' => strpos($detail, "'total'") !== false || strpos($detail, 'total') !== false,
    'cotizaciones.php' => strpos($detail, 'cotizaciones.php') !== false,
    'cotizacion-editar.php?id=' => strpos($detail, 'cotizacion-editar.php?id=') !== false,
    'cotizacion-emitir.php' => strpos($detail, 'cotizacion-emitir.php') !== false,
    'cotizacion-imprimir.php?id=' => strpos($detail, 'cotizacion-imprimir.php?id=') !== false,
    'cotizacion-pdf.php?id=' => strpos($detail, 'cotizacion-pdf.php?id=') !== false,
    'Descargar PDF' => strpos($detail, 'Descargar PDF') !== false,
    'Vista imprimible' => strpos($detail, 'Vista imprimible') !== false,
    'csrf_token' => strpos($detail, 'csrf') !== false || strpos($detail, 'Csrf') !== false,
    'ViewFormatter::e' => strpos($detail, 'ViewFormatter::e(') !== false,
    'borrador' => strpos($detail, "'borrador'") !== false || strpos($detail, 'borrador') !== false,
    'emitida' => strpos($detail, "'emitida'") !== false || strpos($detail, 'emitida') !== false,
    'detalles' => strpos($detail, 'Detalles') !== false || strpos($detail, 'details') !== false,
    'descripcion' => strpos($detail, 'descripcion') !== false,
    'cantidad' => strpos($detail, 'Cantidad') !== false || strpos($detail, 'cantidad') !== false,
    'precio_unitario_neto' => strpos($detail, 'precio_unitario_neto') !== false,
    'total_linea_neto' => strpos($detail, 'total_linea_neto') !== false,
    'quote-detail-* css' => (strpos($css, 'quote-detail-heading') !== false || strpos($css, 'quote-detail-summary') !== false),
    'forbidden_fetch' => (strpos($detail, 'fetch(') === false),
    'forbidden_XHR' => (strpos($detail, 'XMLHttpRequest') === false),
    'forbidden_json' => (strpos($detail, 'application/json') === false),
    'forbidden_mail' => (strpos($detail, 'mail(') === false),
    'forbidden_file_put' => (strpos($detail, 'file_put_contents') === false),
    'forbidden_readfile' => (strpos($detail, 'readfile(') === false),
    'forbidden_stream' => (strpos($detail, 'stream(') === false),
];

$fails = [];
foreach ($checks as $k => $ok) {
    if (!$ok) {
        $fails[] = $k;
    }
}

if ($fails === []) {
    echo "[OK] Orden visual del detalle de cotización cumple el contrato esperado.\n";
    exit(0);
}

echo "[FAIL] El contrato no se cumple. Faltan o fallan comprobaciones:\n";
foreach ($fails as $f) {
    echo " - $f\n";
}

exit(3);
