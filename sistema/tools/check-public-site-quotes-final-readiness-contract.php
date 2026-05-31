<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

$base = dirname(__DIR__);
$publicPaths = [
    'login' => $base . '/public/login.php',
    'quotes' => $base . '/public/cotizaciones.php',
    'detail' => $base . '/public/cotizacion-detalle.php',
    'edit' => $base . '/public/cotizacion-editar.php',
    'print' => $base . '/public/cotizacion-imprimir.php',
    'pdf' => $base . '/public/cotizacion-pdf.php',
    'issue' => $base . '/public/cotizacion-emitir.php',
    'update' => $base . '/public/cotizacion-actualizar.php',
];

$requiredPaths = array_merge($publicPaths, [
    'company' => $base . '/config/company.php',
    'composer' => dirname($base) . '/composer.json',
    'composer_lock' => dirname($base) . '/composer.lock',
    'service_quote_pdf' => $base . '/app/Services/QuotePdfService.php',
    'service_quote_pdf_render' => $base . '/app/Services/QuotePdfRenderService.php',
    'service_quote_pdf_html' => $base . '/app/Services/QuotePdfHtmlBuilder.php',
    'support_company_profile' => $base . '/app/Support/CompanyProfile.php',
]);

$errors = [];
$warnings = [];

foreach ($requiredPaths as $name => $path) {
    if (!file_exists($path)) {
        $errors[] = "missing required file: {$path}";
    }
}

if (!empty($errors)) {
    echo "[FAIL] Contrato no cumplido:\n";
    foreach ($errors as $error) {
        echo " - {$error}\n";
    }
    exit(3);
}

$listing = file_get_contents($publicPaths['quotes']);
$detail = file_get_contents($publicPaths['detail']);
$edit = file_get_contents($publicPaths['edit']);
$pdf = file_get_contents($publicPaths['pdf']);
$company = file_get_contents($requiredPaths['company']);
$composerJson = json_decode(file_get_contents($requiredPaths['composer']), true);

if (!is_array($composerJson)) {
    $errors[] = 'composer.json is not valid JSON';
}

// Vendor/autoload check
$rootVendor = dirname($base) . '/vendor';
$autoloadFile = $rootVendor . '/autoload.php';
if (is_dir($rootVendor)) {
    if (!file_exists($autoloadFile)) {
        $warnings[] = "vendor directory exists but vendor/autoload.php is missing. En despliegue debe ejecutarse composer install o subir vendor como artefacto.";
    }
} else {
    $warnings[] = "vendor directory no existe. En despliegue debe ejecutarse composer install o subir vendor como artefacto.";
}

// Dompdf in composer
if (is_array($composerJson)) {
    $requires = $composerJson['require'] ?? [];
    if (!isset($requires['dompdf/dompdf'])) {
        $errors[] = 'composer.json does not require dompdf/dompdf';
    }
}

// Company values warning
if (stripos($company, 'Pendiente') !== false) {
    $warnings[] = 'sistema/config/company.php contiene valores Pendiente. Revisar datos comerciales antes de producción.';
}

// Frontend view checks
$checks = [
    ['file' => 'quotes', 'needle' => 'Crear borrador'],
    ['file' => 'quotes', 'needle' => 'cotizacion-detalle.php?id='],
    ['file' => 'quotes', 'needle' => 'cotizacion-editar.php?id='],
    ['file' => 'quotes', 'needle' => 'csrf'],
    ['file' => 'detail', 'needle' => 'Detalle de cotización'],
    ['file' => 'detail', 'needle' => 'Descargar PDF'],
    ['file' => 'detail', 'needle' => 'Vista imprimible'],
    ['file' => 'detail', 'needle' => 'cotizacion-pdf.php?id='],
    ['file' => 'detail', 'needle' => 'cotizacion-imprimir.php?id='],
    ['file' => 'edit', 'needle' => 'Editar cotización'],
    ['file' => 'edit', 'needle' => 'method="post"'],
    ['file' => 'edit', 'needle' => 'cotizacion-actualizar.php'],
    ['file' => 'edit', 'needle' => 'csrf'],
];

foreach ($checks as $check) {
    $content = ${$check['file']};
    if (stripos($content, $check['needle']) === false) {
        $errors[] = "{$check['file']}: missing {$check['needle']}";
    }
}

if (stripos($pdf, 'application/pdf') === false && stripos($pdf, 'application/pdf') === false) {
    $errors[] = 'cotizacion-pdf.php does not contain application/pdf';
}
if (stripos($pdf, 'Dompdf') === false && stripos($pdf, 'QuotePdfRenderService') === false) {
    $errors[] = 'cotizacion-pdf.php does not reference Dompdf or QuotePdfRenderService';
}

// Forbidden patterns in sistema/public PHP files
$forbidden = ['fetch(', 'XMLHttpRequest', 'application/json', 'mail(', 'file_put_contents', 'eval(', 'base64_decode('];
$phpFiles = glob($base . '/public/*.php');
foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    foreach ($forbidden as $pattern) {
        if (stripos($content, $pattern) !== false) {
            $errors[] = basename($phpFile) . ": forbidden pattern found: {$pattern}";
        }
    }
}

// No new JS files under sistema/public/assets/js
$jsDir = $base . '/public/assets/js';
if (is_dir($jsDir)) {
    $jsFiles = array_values(array_filter(scandir($jsDir), static fn($name) => is_file($jsDir . '/' . $name)));
    if (!empty($jsFiles)) {
        $warnings[] = 'Existen archivos JS en sistema/public/assets/js; verificar que no sean nuevos ni necesarios para este cierre.';
    }
}

if (!empty($warnings)) {
    foreach ($warnings as $warning) {
        echo "[WARN] {$warning}\n";
    }
}

if (!empty($errors)) {
    echo "[FAIL] Contrato no cumplido:\n";
    foreach ($errors as $error) {
        echo " - {$error}\n";
    }
    exit(3);
}

echo "[OK] Cierre técnico del sitio público y módulo de cotizaciones cumple el contrato esperado.\n";
exit(0);
