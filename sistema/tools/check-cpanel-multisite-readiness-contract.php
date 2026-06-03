<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$build = $root . '/build/cpanel-production';

assertDirectoryExists($build, 'build/cpanel-production');

foreach ([
    'index.html',
    '.htaccess',
    'robots.txt',
    'sitemap.xml',
] as $file) {
    assertFileExists($build . '/' . $file, 'build/cpanel-production/' . $file);
}

foreach ([
    'assets',
    'forms',
    'config',
    'vendor',
] as $directory) {
    assertDirectoryExists($build . '/' . $directory, 'build/cpanel-production/' . $directory);
}

foreach ([
    'docs',
    'sistema/tools',
    '.git',
    '.github',
] as $forbidden) {
    assertPathMissing($build . '/' . $forbidden, 'build/cpanel-production no debe contener ' . $forbidden . '.');
}

$index = readFileOrFail($build . '/index.html', 'build/cpanel-production/index.html');
$htaccess = readFileOrFail($build . '/.htaccess', 'build/cpanel-production/.htaccess');
$robots = readFileOrFail($build . '/robots.txt', 'build/cpanel-production/robots.txt');
$sitemap = readFileOrFail($build . '/sitemap.xml', 'build/cpanel-production/sitemap.xml');

assertContains($index, 'assets/', 'index.html del paquete debe usar rutas relativas a assets/.');
assertContains($index, 'forms/contact.php', 'index.html del paquete debe usar forms/contact.php.');
assertContains($sitemap, 'https://www.dasystems.cl/', 'sitemap.xml debe contener https://www.dasystems.cl/.');
assertContains($robots, 'Sitemap: https://www.dasystems.cl/sitemap.xml', 'robots.txt debe contener Sitemap correcto.');
assertContains($htaccess, 'Options -Indexes', '.htaccess debe contener Options -Indexes.');

foreach (['docs', 'storage', 'sistema/tools', 'sistema/config', 'config'] as $blockedPath) {
    assertContains($htaccess, $blockedPath, '.htaccess debe contener bloqueo para ' . $blockedPath . '.');
}

$combined = $index . "\n" . $htaccess . "\n" . $robots . "\n" . $sitemap;
assertNotContains($combined, 'dasystemstechnology@gmail.com', 'No debe aparecer dasystemstechnology@gmail.com.');

foreach (['Ãƒ', 'Ã‚', 'Ã¢â‚¬', 'Ã¢â€“', 'Ã¢â€”', 'Ã¢â‚¬â„¢', 'Ã¢â‚¬Å“', 'ï¿½', '�'] as $mojibake) {
    assertNotContains($combined, $mojibake, 'No debe haber mojibake: ' . $mojibake);
}

echo "[OK] Preparación multi-sitio cPanel validada correctamente.\n";

function readFileOrFail(string $path, string $label): string
{
    assertFileExists($path, $label);

    $content = file_get_contents($path);
    if ($content === false) {
        fail('No fue posible leer ' . $label . '.');
    }

    return $content;
}

function assertFileExists(string $path, string $label): void
{
    if (!is_file($path)) {
        fail('No existe archivo requerido: ' . $label . '.');
    }
}

function assertDirectoryExists(string $path, string $label): void
{
    if (!is_dir($path)) {
        fail('No existe directorio requerido: ' . $label . '.');
    }
}

function assertPathMissing(string $path, string $message): void
{
    if (file_exists($path)) {
        fail($message);
    }
}

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

function fail(string $message): void
{
    echo '[ERROR] ' . $message . "\n";
    exit(1);
}
