<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$optimizedImages = [
    'assets/img/portfolio/portfolio-11',
    'assets/img/portfolio/portfolio-9',
    'assets/img/about/about-square-8',
    'assets/img/portfolio/portfolio-7',
    'assets/img/portfolio/portfolio-portrait-5',
    'assets/img/portfolio/portfolio-8',
    'assets/img/portfolio/portfolio-3',
    'assets/img/about/about-8',
];

$publicPages = [
    'index.html',
    'nosotros.html',
    'servicios-ti.html',
    'soluciones-ti.html',
    'politica-privacidad.html',
    'terminos-condiciones.html',
];

$html = '';

foreach ($publicPages as $page) {
    $path = $root . '/' . $page;

    if (!is_file($path)) {
        fail('No existe ' . $page . '.');
    }

    $content = file_get_contents($path);

    if ($content === false) {
        fail('No fue posible leer ' . $page . '.');
    }

    $html .= "\n" . $content;

    assertNotContains($content, 'D&amp;A Systems SpA', $page . ' no debe contener D&amp;A Systems SpA.');
    assertNotContains(html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'D&A Systems SpA', $page . ' no debe contener D&A Systems SpA.');
    assertNotContains($content, 'dasystemstechnology@gmail.com', $page . ' no debe contener correo antiguo.');

    foreach (['Ãƒ', 'Ã‚', 'Ã¢â‚¬', 'Ã¢â€“', 'Ã¢â€”', 'Ã¢â‚¬â„¢', 'Ã¢â‚¬Å“', '�'] as $mojibake) {
        assertNotContains($content, $mojibake, $page . ' no debe contener mojibake: ' . $mojibake);
    }
}

assertContains($html, 'contacto@dasystems.cl', 'El sitio publico debe contener contacto@dasystems.cl.');
assertContains($html, 'assets/img/about/about-8.webp', 'index.html debe usar about-8.webp para el Hero.');
assertContains($html, 'assets/img/about/about-square-8.webp', 'El sitio debe usar about-square-8.webp.');

foreach ($optimizedImages as $basePath) {
    $png = $root . '/' . $basePath . '.png';
    $webp = $root . '/' . $basePath . '.webp';
    $oldName = basename($basePath) . '.png';
    $newName = basename($basePath) . '.webp';

    if (!is_file($png)) {
        fail('No existe PNG original: ' . $basePath . '.png.');
    }

    if (!is_file($webp)) {
        fail('No existe WebP optimizado: ' . $basePath . '.webp.');
    }

    $pngSize = filesize($png);
    $webpSize = filesize($webp);

    if ($pngSize === false || $webpSize === false) {
        fail('No fue posible leer tamanos para ' . $basePath . '.');
    }

    if ($webpSize >= $pngSize) {
        fail($newName . ' debe pesar menos que ' . $oldName . '.');
    }

    if ($webpSize > 500 * 1024) {
        echo '[WARNING] ' . $newName . ' supera 500 KB.' . "\n";
    }

    assertNotContains($html, $oldName, 'No debe quedar referencia HTML a ' . $oldName . '.');
}

foreach (['portfolio-11.webp', 'portfolio-9.webp', 'portfolio-7.webp', 'portfolio-portrait-5.webp', 'portfolio-8.webp', 'portfolio-3.webp'] as $portfolioImage) {
    assertContains($html, $portfolioImage, 'El portfolio debe referenciar ' . $portfolioImage . '.');
}

assertImageRoutesExist($root, $html);

echo "[OK] Optimización de imágenes públicas validada correctamente.\n";

function assertImageRoutesExist(string $root, string $html): void
{
    preg_match_all('/<(?:img|link)\b[^>]*(?:src|href)="([^"]+\.(?:png|webp|jpg|jpeg|svg|ico))"/i', $html, $matches);

    foreach ($matches[1] as $route) {
        if (preg_match('/^(?:https?:|data:|mailto:|#)/i', $route) === 1) {
            continue;
        }

        $path = strtok($route, '?');

        if (!is_string($path) || $path === '') {
            continue;
        }

        $fullPath = realpath($root . '/' . ltrim($path, '/'));

        if ($fullPath === false || !is_file($fullPath)) {
            fail('Ruta de imagen rota: ' . $route . '.');
        }
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
