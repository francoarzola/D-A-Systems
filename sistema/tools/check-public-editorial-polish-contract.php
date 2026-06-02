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
];

$contents = [];

foreach ($pages as $page) {
    $path = $root . '/' . $page;

    if (!is_file($path)) {
        fail('No existe ' . $page . '.');
    }

    $content = file_get_contents($path);

    if ($content === false) {
        fail('No fue posible leer ' . $page . '.');
    }

    $contents[$page] = $content;

    assertH1Count($content, $page, 1);
    assertContains($content, 'contacto@dasystems.cl', $page . ' debe contener contacto@dasystems.cl.');
    assertNotContains($content, 'D&amp;A Systems SpA', $page . ' no debe contener D&amp;A Systems SpA.');
    assertNotContains(html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'D&A Systems SpA', $page . ' no debe contener D&A Systems SpA.');
    assertNotContains($content, 'dasystemstechnology@gmail.com', $page . ' no debe contener correo antiguo.');

    foreach (['Ã', 'Â', 'â€', 'â–', 'â—', 'â€™', 'â€œ', '�'] as $mojibake) {
        assertNotContains($content, $mojibake, $page . ' no debe contener mojibake: ' . $mojibake);
    }
}

assertContains($contents['index.html'], 'revisamos equipos, redes, respaldos y sistemas críticos', 'index.html debe contener el nuevo texto del hero.');
assertContains($contents['index.html'], 'te indicamos qué revisar primero y qué puede esperar', 'index.html debe contener la nueva nota bajo CTA.');
assertContains($contents['index.html'], 'Identificamos riesgos, urgencias y acciones recomendadas', 'index.html debe contener la nueva frase de la tarjeta hero.');
assertContains($contents['index.html'], 'la información no dependa de la memoria de alguien', 'index.html debe contener el nuevo texto de Todo documentado.');
assertContains($contents['index.html'], 'Quiero revisar mi situación TI', 'index.html debe conservar el CTA principal.');
assertContains($contents['index.html'], 'Ver cómo trabajamos', 'index.html debe conservar el CTA secundario.');
assertContains($contents['index.html'], 'Soporte TI claro y documentado', 'index.html debe conservar el título de la tarjeta hero.');

assertContains($contents['servicios-ti.html'], 'No tienes que llegar con el diagnóstico hecho', 'servicios-ti.html debe contener la nueva introducción.');
assertContains($contents['servicios-ti.html'], 'lo traducimos en revisión técnica, prioridades y acciones concretas', 'servicios-ti.html debe contener la nueva formulación de revisión técnica.');

assertContains($contents['soluciones-ti.html'], 'De problemas TI repetidos a un plan de acción concreto', 'soluciones-ti.html debe contener el nuevo project-title.');
assertContains($contents['soluciones-ti.html'], 'sin implementar más de lo necesario ni detener la operación', 'soluciones-ti.html debe contener el nuevo subtítulo.');
assertNotContains($contents['soluciones-ti.html'], '<h1 class="project-title">', 'soluciones-ti.html no debe contener <h1 class="project-title">.');

assertContains($contents['nosotros.html'], 'soporte técnico responsable, registro de lo', 'nosotros.html debe contener el nuevo texto de misión.');
assertContains($contents['nosotros.html'], 'realizado y recomendaciones comprensibles', 'nosotros.html debe contener el nuevo texto de misión.');

echo "[OK] Pulido editorial del sitio público validado correctamente.\n";

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
