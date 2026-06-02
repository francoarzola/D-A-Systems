<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$pages = [
    'index.html',
    'servicios-ti.html',
    'soluciones-ti.html',
    'nosotros.html',
    'politica-privacidad.html',
    'terminos-condiciones.html',
];

foreach ($pages as $page) {
    $path = $root . '/' . $page;

    if (!is_file($path)) {
        fail('No existe ' . $page . '.');
    }

    $content = file_get_contents($path);

    if ($content === false) {
        fail('No fue posible leer ' . $page . '.');
    }

    assertH1Count($content, $page, 1);
    assertContains($content, 'contacto@dasystems.cl', $page . ' debe contener contacto@dasystems.cl.');
    assertNotContains($content, 'D&amp;A Systems SpA', $page . ' no debe contener D&amp;A Systems SpA.');
    assertNotContains(html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'D&A Systems SpA', $page . ' no debe contener D&A Systems SpA.');
    assertNotContains($content, 'dasystemstechnology@gmail.com', $page . ' no debe contener correo antiguo.');

    foreach (['Ã', 'Â', 'â€', 'â–', 'â—', 'â€™', 'â€œ', '�'] as $mojibake) {
        assertNotContains($content, $mojibake, $page . ' no debe contener mojibake: ' . $mojibake);
    }
}

$solutions = file_get_contents($root . '/soluciones-ti.html');

if ($solutions === false) {
    fail('No fue posible leer soluciones-ti.html.');
}

assertH1Count($solutions, 'soluciones-ti.html', 1);
assertContains($solutions, '<h2 class="project-title">', 'soluciones-ti.html debe contener <h2 class="project-title">.');
assertNotContains($solutions, '<h1 class="project-title">', 'soluciones-ti.html no debe contener <h1 class="project-title">.');

echo "[OK] Jerarquía de encabezados del sitio público validada correctamente.\n";

function assertH1Count(string $content, string $page, int $expected): void
{
    preg_match_all('/<h1\b/i', $content, $matches);
    $count = count($matches[0]);

    if ($count !== $expected) {
        fail($page . ' debe tener exactamente ' . $expected . ' <h1>. Encontrados: ' . $count . '.');
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
