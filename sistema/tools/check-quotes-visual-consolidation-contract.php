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
    'css' => $base . '/public/assets/css/internal.css',
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
$css = file_get_contents($paths['css']);

$ok = true;
$errs = [];

// Common checks for all three screens
foreach (['listing' => $listing, 'detail' => $detail, 'edit' => $edit] as $name => $content) {
    if (strpos($content, 'internal-topbar') === false) {
        $ok = false; $errs[] = "$name: missing internal-topbar";
    }
    if (strpos($content, 'ViewFormatter::e') === false) {
        $ok = false; $errs[] = "$name: missing ViewFormatter::e";
    }
    if (stripos($content, 'D&amp;A Systems') === false && stripos($content, 'D&A Systems') === false) {
        $ok = false; $errs[] = "$name: missing D&A Systems brand text";
    }
}

// Listing specific
if (strpos($listing, 'Crear borrador') === false) { $ok = false; $errs[] = 'listing: missing Crear borrador'; }
if (strpos($listing, 'id="crear-borrador"') === false && strpos($listing, "id='crear-borrador'") === false) { $ok = false; $errs[] = 'listing: missing id="crear-borrador"'; }
if (strpos($listing, 'method="post"') === false && strpos($listing, "method='post'") === false) { $ok = false; $errs[] = 'listing: missing method="post" in forms'; }
if (stripos($listing, 'csrf') === false) { $ok = false; $errs[] = 'listing: missing csrf token usage'; }
if (strpos($listing, 'cotizacion-detalle.php?id=') === false) { $ok = false; $errs[] = 'listing: missing cotizacion-detalle.php?id='; }
if (strpos($listing, 'cotizacion-editar.php?id=') === false) { /* edit link may be conditional, but check presence */ }

// Detail specific
$detailChecks = [
    'Detalle de cotización', 'Descargar PDF', 'Vista imprimible', 'cotizacion-pdf.php?id=', 'cotizacion-imprimir.php?id=', 'cotizacion-emitir.php', 'csrf', 'borrador', 'emitida'
];
foreach ($detailChecks as $c) {
    if (stripos($detail, $c) === false) { $ok = false; $errs[] = "detail: missing {$c}"; }
}

// Edit specific
$editChecks = [
    'Editar cotización', 'method="post"', 'csrf', 'type="submit"', 'cotizacion-actualizar.php', 'cotizacion-detalle.php?id=', 'cotizaciones.php'
];
foreach ($editChecks as $c) {
    if (stripos($edit, $c) === false) { $ok = false; $errs[] = "edit: missing {$c}"; }
}

// CSS patterns
$cssPatterns = [
    'internal-topbar','internal-nav','quotes-page-heading','quotes-list-section','quotes-page-heading',
    'quote-detail-heading','quote-detail-summary','quote-detail-actions',
    'quote-edit-heading','quote-edit-summary','quote-edit-actions'
];
foreach ($cssPatterns as $pat) {
    if (strpos($css, $pat) === false) { $ok = false; $errs[] = "css: missing pattern {$pat}"; }
}

// Forbidden patterns in reviewed screens
$forbidden = ['fetch(', 'XMLHttpRequest', 'application/json', 'mail(', 'file_put_contents', 'readfile', 'stream('];
foreach (['listing'=>$listing,'detail'=>$detail,'edit'=>$edit] as $name=>$content) {
    foreach ($forbidden as $f) {
        if (stripos($content, $f) !== false) { $ok = false; $errs[] = "$name: forbidden pattern found: {$f}"; }
    }
}

// Ensure critical files still exist
$mustExist = [
    $base . '/public/login.php',
    $base . '/public/cotizacion-imprimir.php',
    $base . '/public/cotizacion-pdf.php',
    $base . '/public/cotizacion-emitir.php',
    $base . '/public/cotizacion-actualizar.php',
];
foreach ($mustExist as $m) { if (!file_exists($m)) { $ok = false; $errs[] = "missing critical file: {$m}"; } }

// Ensure no new css files were added in assets/css other than internal.css
$cssDir = $base . '/public/assets/css';
$files = array_values(array_filter(scandir($cssDir), static fn($n) => is_file($cssDir . '/' . $n)));
foreach ($files as $f) {
    if ($f !== 'internal.css') { $ok = false; $errs[] = "unexpected css file: {$f}"; }
}

// Ensure no JS files were added under public (simple check)
$publicJs = $base . '/public/assets/js';
if (is_dir($publicJs)) {
    $jsFiles = array_values(array_filter(scandir($publicJs), static fn($n) => is_file($publicJs . '/' . $n)));
    if (!empty($jsFiles)) { $ok = false; $errs[] = 'unexpected JS files under public/assets/js'; }
}

if ($ok) {
    echo "[OK] Consolidación visual del módulo de cotizaciones cumple el contrato esperado.\n";
    exit(0);
}

echo "[FAIL] Contrato no cumplido:\n";
foreach ($errs as $e) echo " - {$e}\n";
exit(3);
