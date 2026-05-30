<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

$base = __DIR__ . '/..';
$paths = [
    'listing' => $base . '/public/cotizaciones.php',
    'detail' => $base . '/public/cotizacion-detalle.php',
    'edit' => $base . '/public/cotizacion-editar.php',
];

$missing = [];
foreach ($paths as $p) {
    if (!file_exists($p)) $missing[] = $p;
}

if (!empty($missing)) {
    echo "[FAIL] Missing required files:\n" . implode("\n", $missing) . "\n";
    exit(2);
}

$listing = file_get_contents($paths['listing']);
$detail = file_get_contents($paths['detail']);
$edit = file_get_contents($paths['edit']);

$ok = true;
$errs = [];

// Key texts to verify
$keyTexts = [
    'Cotizaciones',
    'Crear borrador',
    'Detalle de cotización',
    'Editar cotización',
    'Guardar cambios',
    'Vista imprimible',
    'Descargar PDF',
    'Volver al listado'
];

foreach ($keyTexts as $text) {
    if (stripos($listing, $text) === false && stripos($detail, $text) === false && stripos($edit, $text) === false) {
        $ok = false; $errs[] = "missing key text: {$text}";
    }
}

// Functional elements
$functionalElements = [
    'method="post"', 'csrf', 'cotizacion-detalle.php?id=', 'cotizacion-editar.php?id=',
    'cotizacion-pdf.php?id=', 'cotizacion-imprimir.php?id=', 'cotizacion-emitir.php', 'cotizacion-actualizar.php',
    'ViewFormatter::e'
];

foreach ($functionalElements as $elem) {
    if (stripos($listing, $elem) === false && stripos($detail, $elem) === false && stripos($edit, $elem) === false) {
        $ok = false; $errs[] = "missing functional element: {$elem}";
    }
}

// Forbidden patterns
$forbidden = ['fetch(', 'XMLHttpRequest', 'application/json', 'mail(', 'file_put_contents', 'readfile', 'stream('];
foreach (['listing'=>$listing,'detail'=>$detail,'edit'=>$edit] as $name=>$content) {
    foreach ($forbidden as $f) {
        if (stripos($content, $f) !== false) { $ok = false; $errs[] = "{$name}: forbidden pattern: {$f}"; }
    }
}

// Critical files must still exist
$criticalFiles = [
    $base . '/public/cotizacion-imprimir.php',
    $base . '/public/cotizacion-pdf.php',
    $base . '/public/cotizacion-emitir.php',
    $base . '/public/cotizacion-actualizar.php',
];
foreach ($criticalFiles as $cf) {
    if (!file_exists($cf)) { $ok = false; $errs[] = "missing critical file: {$cf}"; }
}

// No new CSS files
$cssDir = $base . '/public/assets/css';
if (is_dir($cssDir)) {
    $files = array_values(array_filter(scandir($cssDir), static fn($n) => is_file($cssDir . '/' . $n)));
    foreach ($files as $f) {
        if ($f !== 'internal.css') { $ok = false; $errs[] = "unexpected css file: {$f}"; }
    }
}

// No new JS files
$jsDir = $base . '/public/assets/js';
if (is_dir($jsDir)) {
    $jsFiles = array_values(array_filter(scandir($jsDir), static fn($n) => is_file($jsDir . '/' . $n)));
    if (!empty($jsFiles)) { $ok = false; $errs[] = 'unexpected JS files in public/assets/js'; }
}

if ($ok) {
    echo "[OK] Revisión de textos y microcopy del módulo de cotizaciones cumple el contrato esperado.\n";
    exit(0);
}

echo "[FAIL] Contrato no cumplido:\n";
foreach ($errs as $e) echo " - {$e}\n";
exit(3);
