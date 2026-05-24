<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$pagePath = __DIR__ . '/../public/cotizaciones.php';
$cssPath = __DIR__ . '/../public/assets/css/internal.css';

$pageContents = readFileOrFail($pagePath, 'cotizaciones.php');
$cssContents = readFileOrFail($cssPath, 'internal.css');

$pageFragments = [
    'FlashMessage',
    'pull()',
    'flash-message',
    'normalizeFlashType',
    'ViewFormatter::e',
    'flash-message-<?php echo ViewFormatter::e($flashType); ?>',
];

foreach ($pageFragments as $fragment) {
    if (!str_contains($pageContents, $fragment)) {
        outputError("Falta referencia esperada en cotizaciones.php: {$fragment}");
        exit(1);
    }
}

$cssFragments = [
    '.flash-message',
    '.flash-message-success',
    '.flash-message-error',
    '.flash-message-warning',
    '.flash-message-info',
];

foreach ($cssFragments as $fragment) {
    if (!str_contains($cssContents, $fragment)) {
        outputError("Falta clase esperada en internal.css: {$fragment}");
        exit(1);
    }
}

outputOk('El contrato visual de mensajes flash está completo.');
outputOk('No se ejecutó POST real ni se usó base de datos.');
exit(0);

function readFileOrFail(string $path, string $label): string
{
    if (!is_file($path)) {
        outputError("No existe {$label}.");
        exit(1);
    }

    $contents = file_get_contents($path);

    if (!is_string($contents) || $contents === '') {
        outputError("No fue posible leer {$label}.");
        exit(1);
    }

    return $contents;
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
