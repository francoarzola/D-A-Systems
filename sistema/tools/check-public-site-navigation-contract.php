<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$publicPages = [
    'index.html',
    'nosotros.html',
    'servicios-ti.html',
    'soluciones-ti.html',
    'politica-privacidad.html',
    'terminos-condiciones.html',
];

$contents = [];

foreach ($publicPages as $page) {
    $path = $root . '/' . $page;

    if (!is_file($path)) {
        fail('No existe la página pública principal: ' . $page);
    }

    $content = file_get_contents($path);

    if ($content === false) {
        fail('No fue posible leer: ' . $page);
    }

    $contents[$page] = $content;
}

assertContains($contents['index.html'], '<nav id="navmenu"', 'index.html debe mantener navegación principal.');
assertContains($contents['index.html'], 'href="nosotros.html"', 'index.html debe enlazar visiblemente a nosotros.html en el nav principal.');

$internalPages = [
    'nosotros.html',
    'servicios-ti.html',
    'soluciones-ti.html',
    'politica-privacidad.html',
    'terminos-condiciones.html',
];

foreach ($internalPages as $page) {
    $content = $contents[$page];

    assertContains($content, 'href="index.html#hero"', $page . ' debe enlazar a index.html#hero.');
    assertAnyContains($content, ['href="servicios-ti.html"', 'href="index.html#servicios-ti"'], $page . ' debe enlazar a Servicios.');
    assertAnyContains($content, ['href="soluciones-ti.html"', 'href="index.html#soluciones-ti"'], $page . ' debe enlazar a Soluciones.');
    assertContains($content, 'href="nosotros.html"', $page . ' debe enlazar a nosotros.html.');
    assertContains($content, 'href="index.html#contact"', $page . ' debe enlazar a contacto.');
}

foreach (['politica-privacidad.html', 'terminos-condiciones.html'] as $legalPage) {
    assertContains($contents[$legalPage], 'terminos-condiciones.html', $legalPage . ' debe enlazar términos desde el footer.');
    assertContains($contents[$legalPage], 'politica-privacidad.html', $legalPage . ' debe enlazar política desde el footer.');
}

$sitemapPath = $root . '/sitemap.xml';
$robotsPath = $root . '/robots.txt';

if (!is_file($sitemapPath) || !is_file($robotsPath)) {
    fail('sitemap.xml y robots.txt deben existir.');
}

$sitemap = file_get_contents($sitemapPath);
$robots = file_get_contents($robotsPath);

if ($sitemap === false || $robots === false) {
    fail('No fue posible leer sitemap.xml o robots.txt.');
}

foreach ([
    'https://www.dasystems.cl/',
    'servicios-ti.html',
    'soluciones-ti.html',
    'nosotros.html',
    'politica-privacidad.html',
    'terminos-condiciones.html',
] as $fragment) {
    assertContains($sitemap, $fragment, 'sitemap.xml debe contener: ' . $fragment);
}

assertContains($robots, 'Sitemap: https://www.dasystems.cl/sitemap.xml', 'robots.txt debe apuntar al sitemap.');

foreach ($contents as $page => $content) {
    $decodedContent = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    assertNotContains($decodedContent, 'D&A Systems SpA', $page . ' no debe contener D&A Systems SpA.');
    assertNotContains($content, 'D&amp;A Systems SpA', $page . ' no debe contener D&amp;A Systems SpA.');
    assertNotContains($content, 'dasystemstechnology@gmail.com', $page . ' no debe contener correo antiguo.');
    assertContains($content, 'contacto@dasystems.cl', $page . ' debe contener contacto@dasystems.cl.');
    assertContains($content, 'favicon-dasystems.png', $page . ' debe referenciar favicon-dasystems.png.');
    assertContains($content, 'apple-touch-icon.png', $page . ' debe referenciar apple-touch-icon.png.');
    foreach (['portfolio-11.png', 'portfolio-9.png', 'about-square-8.png', 'portfolio-7.png', 'portfolio-portrait-5.png', 'portfolio-8.png', 'portfolio-3.png', 'about-8.png'] as $legacyImage) {
        assertNotContains($content, $legacyImage, $page . ' no debe contener la imagen PNG pesada antigua: ' . $legacyImage . '.');
    }

    foreach (['Ã', 'Â', 'â€', 'â–', 'â—', 'â€™', 'â€œ', '�'] as $mojibake) {
        assertNotContains($content, $mojibake, $page . ' no debe contener mojibake: ' . $mojibake);
    }
}

echo "[OK] Navegación pública del sitio validada correctamente.\n";

function assertContains(string $content, string $needle, string $message): void
{
    if (strpos($content, $needle) === false) {
        fail($message);
    }
}

function assertAnyContains(string $content, array $needles, string $message): void
{
    foreach ($needles as $needle) {
        if (strpos($content, $needle) !== false) {
            return;
        }
    }

    fail($message);
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
