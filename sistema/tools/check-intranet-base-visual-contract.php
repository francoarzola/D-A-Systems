<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$loginPath = $rootPath . '/public/login.php';
$cssPath = $rootPath . '/public/assets/css/internal.css';
$requiredExistingFiles = [
    $rootPath . '/public/cotizaciones.php',
    $rootPath . '/public/cotizacion-detalle.php',
    $rootPath . '/public/cotizacion-pdf.php',
];

$errors = [];
$login = readFileContents($loginPath, $errors);
$css = readFileContents($cssPath, $errors);

assertContains($login, [
    '<form',
    'method="post"',
    'action="login.php"',
    'csrf_token',
    'name="email"',
    'name="password"',
    'type="submit"',
], 'login.php', $errors);

if (preg_match('/<button\b(?=[^>]*type="submit")(?=[^>]*button-disabled)/i', $login) === 1) {
    $errors[] = 'login.php usa button-disabled en el botón submit funcional.';
}

assertContains($css, [
    '.button-primary',
    'form.card label',
    'form.card input[type="email"]',
    '.status-panel',
    '.flash-message',
    '.card',
    '.container',
    '.header',
], 'internal.css', $errors);

foreach ($requiredExistingFiles as $file) {
    if (!is_file($file)) {
        $errors[] = 'No existe archivo esperado fuera de alcance: ' . displayPath($file) . '.';
    }
}

assertDoesNotContain($login . "\n" . $css, [
    'XMLHttpRequest',
    'application/json',
], 'normalización visual base', $errors);
assertDoesNotMatch($login . "\n" . $css, '/(?<![-A-Za-z0-9_>])fetch\s*\(/', 'normalización visual base', 'fetch(', $errors);
assertDoesNotMatch($login . "\n" . $css, '/(?<![A-Za-z0-9_])\\\\?mail\s*\(/', 'normalización visual base', 'mail(', $errors);

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

echo "[OK] Normalización visual base de intranet cumple el contrato esperado.\n";
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
