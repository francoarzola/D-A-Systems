<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from CLI only.\n";
    exit(1);
}

$root = dirname(dirname(__DIR__));
$buildPath = $root . '/build/cpanel-production';

$errors = [];
$warnings = [];

function checkPath(string $path, bool $mustExist = true): bool
{
    if ($mustExist) {
        return file_exists($path);
    }
    return !file_exists($path);
}

function normalizePath(string $path): string
{
    return str_replace('\\', '/', $path);
}

if (!is_dir($buildPath)) {
    echo "[FAIL] build/cpanel-production/ no existe. Crear el paquete primero.\n";
    exit(2);
}

$publicPages = [
    'index.html',
    'servicios-ti.html',
    'soluciones-ti.html',
    'nosotros.html',
    'terminos-condiciones.html',
    'politica-privacidad.html',
];

foreach ($publicPages as $page) {
    $pagePath = "$buildPath/$page";
    if (!file_exists($pagePath)) {
        $errors[] = "$page falta en build/cpanel-production";
        continue;
    }

    $content = file_get_contents($pagePath);
    if ($content === false) {
        $errors[] = "No se puede leer $page";
        continue;
    }

    if (preg_match('/\bSpA\b/i', $content)) {
        $errors[] = "$page contiene 'SpA'";
    }

    if (stripos($content, 'favicon D&A Systems.png') !== false || stripos($content, 'favicon%20D&A%20Systems') !== false) {
        $errors[] = "$page contiene favicon antiguo";
    }

    $mojibakePatterns = ['Ã', 'Â', 'â€', 'â–', 'â—', 'â€™', 'â€œ', '�'];
    foreach ($mojibakePatterns as $pattern) {
        if (stripos($content, $pattern) !== false) {
            $errors[] = "$page contiene mojibake: $pattern";
        }
    }
}

$requiredDirs = [
    'assets',
    'forms',
    'config',
    'sistema/public',
];

foreach ($requiredDirs as $dir) {
    if (!is_dir("$buildPath/$dir")) {
        $errors[] = "Falta directorio requerido: $dir";
    }
}

$requiredFiles = [
    'robots.txt',
    'sitemap.xml',
    'composer.json',
    'forms/contact.php',
    'config/contact.php',
];

foreach ($requiredFiles as $file) {
    if (!file_exists("$buildPath/$file")) {
        $errors[] = "Falta archivo requerido: $file";
    }
}

$faviconPath = "$buildPath/assets/img/uploads/favicon-dasystems.png";
if (!file_exists($faviconPath)) {
    $errors[] = 'Falta favicon-dasystems.png en el paquete';
}

$forbiddenPaths = [
    'docs',
    'sistema/tools',
    '.git',
    '.github',
    '.vscode',
    '.env',
    '.env.local',
    'README.md',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($buildPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $relative = substr(normalizePath($item->getPathname()), strlen(normalizePath($buildPath)) + 1);
    foreach ($forbiddenPaths as $forbidden) {
        if ($relative === $forbidden || strpos($relative, $forbidden . '/') === 0) {
            $errors[] = "El paquete contiene ruta prohibida: $relative";
        }
    }
}

$companyPath = "$buildPath/sistema/config/company.php";
if (file_exists($companyPath)) {
    $companyContent = file_get_contents($companyPath);
    if ($companyContent !== false && stripos($companyContent, 'Pendiente') !== false) {
        $warnings[] = 'sistema/config/company.php contiene "Pendiente"';
    }
}

$vendorAutoload = "$buildPath/vendor/autoload.php";
if (!file_exists($vendorAutoload)) {
    $warnings[] = 'vendor/autoload.php no está en el paquete. En producción debe ejecutarse composer install si no se sube vendor completo.';
}

if (!empty($errors)) {
    echo "[FAIL] Paquete de producción cPanel no pasó la validación:\n";
    foreach ($errors as $error) {
        echo " - $error\n";
    }
    exit(2);
}

foreach ($warnings as $warning) {
    echo "[WARN] $warning\n";
}

echo "[OK] Paquete de producción cPanel validado correctamente.\n";
exit(0);
