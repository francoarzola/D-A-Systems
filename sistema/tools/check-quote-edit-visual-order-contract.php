<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

$base = __DIR__ . '/..';
$editPath = $base . '/public/cotizacion-editar.php';
$cssPath = $base . '/public/assets/css/internal.css';

$required = [$editPath, $cssPath, $base . '/public/cotizaciones.php', $base . '/public/cotizacion-detalle.php', $base . '/public/cotizacion-pdf.php', $base . '/public/cotizacion-imprimir.php', $base . '/public/cotizacion-emitir.php'];
$missing = [];
foreach ($required as $f) {
    if (!file_exists($f)) $missing[] = $f;
}
if ($missing) {
    echo "[FAIL] Missing files:\n" . implode("\n", $missing) . "\n";
    exit(2);
}

$edit = file_get_contents($editPath);
$css = file_get_contents($cssPath);

$checks = [
    'internal-topbar' => strpos($edit, 'internal-topbar') !== false,
    'Editar cotización' => strpos($edit, 'Editar borrador de cotización') !== false || strpos($edit, 'Editar cotizaci') !== false,
    'method_post' => strpos($edit, 'method="post"') !== false,
    'csrf' => strpos($edit, 'csrf') !== false || strpos($edit, 'Csrf') !== false,
    'type_submit' => strpos($edit, 'type="submit"') !== false,
    'estado' => strpos($edit, "'estado'") !== false || strpos($edit, 'estado') !== false,
    'borrador' => strpos($edit, 'borrador') !== false,
    'nombre_cliente' => strpos($edit, 'nombre_cliente') !== false,
    'rut_cliente' => strpos($edit, 'rut_cliente') !== false,
    'nombre_contacto' => strpos($edit, 'nombre_contacto') !== false,
    'correo_contacto' => strpos($edit, 'correo_contacto') !== false,
    'telefono_contacto' => strpos($edit, 'telefono_contacto') !== false,
    'descripcion' => strpos($edit, 'descripcion') !== false,
    'condiciones_comerciales' => strpos($edit, 'condiciones_comerciales') !== false,
    'observaciones' => strpos($edit, 'observaciones') !== false,
    'cantidad' => strpos($edit, 'cantidad') !== false,
    'unidad' => strpos($edit, 'unidad') !== false,
    'precio_unitario_neto' => strpos($edit, 'precio_unitario_neto') !== false,
    'descuento_monto' => strpos($edit, 'descuento_monto') !== false,
    'cotizacion-detalle-link' => strpos($edit, 'cotizacion-detalle.php?id=') !== false,
    'cotizaciones.php' => strpos($edit, 'cotizaciones.php') !== false,
    'ViewFormatter::e' => strpos($edit, 'ViewFormatter::e(') !== false,
    'hidden_cotizacion_id' => strpos($edit, 'cotizacion_id') !== false,
    'detalle_id' => strpos($edit, 'detalles') !== false || strpos($edit, 'detalle_id') !== false,
    'errors' => (strpos($edit, 'errors') !== false || strpos($edit, 'error') !== false || strpos($edit, 'flash') !== false),
    'css_helpers' => (strpos($css, 'quote-edit-heading') !== false || strpos($css, 'quote-edit-summary') !== false || strpos($css, 'quote-edit-actions') !== false),
    'forbidden_fetch' => (strpos($edit, 'fetch(') === false),
    'forbidden_XHR' => (strpos($edit, 'XMLHttpRequest') === false),
    'forbidden_json' => (strpos($edit, 'application/json') === false),
    'forbidden_mail' => (strpos($edit, 'mail(') === false),
    'forbidden_file_put' => (strpos($edit, 'file_put_contents') === false),
    'forbidden_readfile' => (strpos($edit, 'readfile(') === false),
    'forbidden_stream' => (strpos($edit, 'stream(') === false),
];

$fails = [];
foreach ($checks as $k => $ok) {
    if (!$ok) $fails[] = $k;
}

if (empty($fails)) {
    echo "[OK] Orden visual del formulario de edición de cotización cumple el contrato esperado.\n";
    exit(0);
}

echo "[FAIL] Contrato no cumplido. Faltan:\n";
foreach ($fails as $f) echo " - $f\n";
exit(3);
