<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$pages = [
    'cotizaciones.php' => $rootPath . '/public/cotizaciones.php',
    'cotizacion-detalle.php' => $rootPath . '/public/cotizacion-detalle.php',
    'cotizacion-editar.php' => $rootPath . '/public/cotizacion-editar.php',
];
$cssPath = $rootPath . '/public/assets/css/internal.css';
$loginPath = $rootPath . '/public/login.php';
$pdfPath = $rootPath . '/public/cotizacion-pdf.php';
$printPath = $rootPath . '/public/cotizacion-imprimir.php';

$errors = [];
$css = readFileContents($cssPath, $errors);

assertContains($css, [
    'internal-topbar',
    'internal-nav',
    'internal-nav-link',
], 'internal.css', $errors);

foreach ($pages as $label => $path) {
    $contents = readFileContents($path, $errors);
    assertContains($contents, [
        'internal-topbar',
        'D&amp;A Systems',
        'Sistema interno',
        'cotizaciones.php',
    ], $label, $errors);
    assertDoesNotContain($contents, [
        'fetch(',
        'XMLHttpRequest',
        'application/json',
    ], $label, $errors);
    assertDoesNotMatch($contents, '/(?<![A-Za-z0-9_])\\\\?mail\s*\(/', $label, 'mail(', $errors);
}

$login = readFileContents($loginPath, $errors);
assertDoesNotContain($login, [
    'internal-topbar',
], 'login.php', $errors);

$pdf = readFileContents($pdfPath, $errors);
assertDoesNotContain($pdf, [
    'internal-topbar',
    'internal-nav',
], 'cotizacion-pdf.php', $errors);

$print = readFileContents($printPath, $errors);
assertDoesNotContain($print, [
    'internal-topbar',
], 'cotizacion-imprimir.php', $errors);

$cssFiles = listFilesByExtension($rootPath . '/public', 'css');
$allowedCss = [normalizePath($cssPath)];

foreach ($cssFiles as $cssFile) {
    if (!in_array(normalizePath($cssFile), $allowedCss, true)) {
        $errors[] = 'Existe archivo CSS no esperado: ' . displayPath($cssFile) . '.';
    }
}

$jsFiles = listFilesByExtension($rootPath . '/public', 'js');

foreach ($jsFiles as $jsFile) {
    $errors[] = 'Existe archivo JS no esperado: ' . displayPath($jsFile) . '.';
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] Header y navegación interna cumplen el contrato esperado.\n";
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

function assertDoesNotMatch(string $contents, string $pattern, string $label, string $description, array &$errors): void
{
    if (preg_match($pattern, $contents) === 1) {
        $errors[] = $label . ' contiene fragmento no permitido: ' . $description . '.';
    }
}

function listFilesByExtension(string $directory, string $extension): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
            continue;
        }

        if (strtolower($fileInfo->getExtension()) === strtolower($extension)) {
            $files[] = $fileInfo->getPathname();
        }
    }

    return $files;
}

function normalizePath(string $path): string
{
    return str_replace('\\', '/', $path);
}

function displayPath(string $path): string
{
    $normalized = normalizePath($path);
    $marker = '/D-A-Systems/';
    $position = strpos($normalized, $marker);

    if ($position === false) {
        return $path;
    }

    return substr($normalized, $position + strlen($marker));
}
