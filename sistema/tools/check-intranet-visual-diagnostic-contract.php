<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$projectRoot = dirname($rootPath);
$documentPath = $projectRoot . '/docs/sistema-interno/84-diagnostico-visual-intranet.md';
$reviewedFiles = [
    $rootPath . '/public/login.php',
    $rootPath . '/public/cotizaciones.php',
    $rootPath . '/public/cotizacion-detalle.php',
    $rootPath . '/public/cotizacion-editar.php',
    $rootPath . '/public/cotizacion-imprimir.php',
    $rootPath . '/public/assets/css/internal.css',
];

$errors = [];
$document = readFileContents($documentPath, $errors);

assertContains($document, [
    'Login',
    'Cotizaciones',
    'Detalle de cotización',
    'Edición de cotización',
    'Vista imprimible',
    'internal.css',
    'Diagnóstico por pantalla',
    'Diagnóstico de botones',
    'Diagnóstico de navegación',
    'Diagnóstico de jerarquía visual',
    'Diagnóstico de formularios',
    'Diagnóstico de tablas',
    'Matriz de problemas',
    'Priorización recomendada',
    '7B.02',
    'Qué NO se implementó',
], '84-diagnostico-visual-intranet.md', $errors);

foreach ($reviewedFiles as $file) {
    if (!is_file($file)) {
        $errors[] = 'No existe archivo revisado esperado: ' . displayPath($file) . '.';
    }
}

$cssFiles = listFilesByExtension($rootPath . '/public', 'css');
$allowedCss = [normalizePath($rootPath . '/public/assets/css/internal.css')];

foreach ($cssFiles as $cssFile) {
    if (!in_array(normalizePath($cssFile), $allowedCss, true)) {
        $errors[] = 'Existe archivo CSS no esperado: ' . displayPath($cssFile) . '.';
    }
}

$jsFiles = listFilesByExtension($rootPath . '/public', 'js');

if ($jsFiles !== []) {
    foreach ($jsFiles as $jsFile) {
        $errors[] = 'Existe archivo JS no esperado: ' . displayPath($jsFile) . '.';
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] Diagnóstico visual de intranet cumple el contrato esperado.\n";
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
