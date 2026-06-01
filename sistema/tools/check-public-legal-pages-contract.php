<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$pages = [
    'politica-privacidad.html' => [
        'title' => 'Política de privacidad',
        'forbiddenDuplicateHeading' => '<h2>Política de privacidad</h2>',
    ],
    'terminos-condiciones.html' => [
        'title' => 'Términos y condiciones',
        'forbiddenDuplicateHeading' => '<h2>Términos y condiciones</h2>',
    ],
];

foreach ($pages as $file => $rules) {
    $path = $root . '/' . $file;

    if (!is_file($path)) {
        fail($file . ' no existe.');
    }

    $content = file_get_contents($path);

    if ($content === false) {
        fail('No fue posible leer ' . $file . '.');
    }

    $decodedContent = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    assertExactH1Count($content, $file);
    assertContains($content, $rules['title'], $file . ' debe contener "' . $rules['title'] . '".');
    assertContains($content, '<header id="header"', $file . ' debe mantener header global.');
    assertContains($content, '<main class="main">', $file . ' debe mantener main.');
    assertContains($content, 'class="page-title"', $file . ' debe mantener page-title.');
    assertContains($content, '<footer id="footer"', $file . ' debe mantener footer.');
    assertContains($content, 'rel="canonical"', $file . ' debe mantener canonical.');
    assertContains($content, 'favicon-dasystems.png', $file . ' debe mantener favicon actual.');
    assertContains($content, 'apple-touch-icon.png', $file . ' debe mantener apple-touch-icon actual.');
    assertContains($content, 'contacto@dasystems.cl', $file . ' debe mantener contacto@dasystems.cl.');

    assertNotContains($decodedContent, 'D&A Systems SpA', $file . ' no debe reintroducir D&A Systems SpA.');
    assertNotContains($content, 'D&amp;A Systems SpA', $file . ' no debe reintroducir D&amp;A Systems SpA.');
    assertNotContains($content, 'dasystemstechnology@gmail.com', $file . ' no debe contener correo antiguo.');
    assertNotContains($content, $rules['forbiddenDuplicateHeading'], $file . ' no debe repetir el titulo principal como h2 consecutivo.');
    assertNotContains($content, '<div class="privacy-header" data-aos="fade-up">' . "\n" . '          <div class="header-content">' . "\n" . '            <div class="last-updated">Última actualización: Mayo 2026</div>' . "\n" . '            <h1>', $file . ' no debe duplicar h1 en header interno.');

    foreach (['Ã', 'Â', 'â€', 'â–', 'â—', 'â€™', 'â€œ', '�'] as $mojibake) {
        assertNotContains($content, $mojibake, $file . ' no debe contener mojibake: ' . $mojibake);
    }
}

echo "[OK] Páginas legales públicas validadas correctamente.\n";

function assertExactH1Count(string $content, string $file): void
{
    preg_match_all('/<h1\b/i', $content, $matches);

    if (count($matches[0]) !== 1) {
        fail($file . ' debe tener exactamente un <h1>. Encontrados: ' . count($matches[0]) . '.');
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
