<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$indexPath = $root . '/index.html';

if (!is_file($indexPath)) {
    fail('index.html no existe.');
}

$content = file_get_contents($indexPath);

if ($content === false) {
    fail('No fue posible leer index.html.');
}

assertContains($content, 'class="service-card"', 'Debe existir .service-card.');
assertContains($content, 'service-card-header', 'Debe existir service-card-header.');
assertContains($content, 'Soporte TI claro y documentado', 'Debe existir el nuevo título de tarjeta.');
assertContains($content, 'Identificamos riesgos, urgencias y acciones recomendadas.', 'Debe existir la nueva frase de tarjeta.');
assertContains($content, 'service-card-text', 'Debe existir service-card-text.');

assertNotContains($content, 'Tu tecnología, en manos responsables', 'No debe existir el texto anterior de la tarjeta.');
assertNotContains($content, 'Soporte técnico con registro de cada atención', 'No debe existir la lista antigua de soporte.');
assertNotContains($content, 'Equipos, redes, correos y activos TI administrados', 'No debe existir la lista antigua de activos.');
assertNotContains($content, 'Respaldos activos y continuidad operativa real', 'No debe existir la lista antigua de respaldos.');
assertNotContains($content, '<h1 class="project-title">', 'No debe existir <h1 class="project-title">.');
assertNotContains($content, 'D&amp;A Systems SpA', 'No debe aparecer D&amp;A Systems SpA.');
assertNotContains(html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'D&A Systems SpA', 'No debe aparecer D&A Systems SpA.');
assertNotContains($content, 'dasystemstechnology@gmail.com', 'No debe aparecer dasystemstechnology@gmail.com.');
assertContains($content, 'contacto@dasystems.cl', 'Debe aparecer contacto@dasystems.cl.');

foreach (['Ãƒ', 'Ã‚', 'Ã¢â‚¬', 'Ã¢â€“', 'Ã¢â€”', 'Ã¢â‚¬â„¢', 'Ã¢â‚¬Å“', '�'] as $mojibake) {
    assertNotContains($content, $mojibake, 'No debe haber mojibake: ' . $mojibake);
}

assertContains($content, 'Soporte y gestión TI para tu empresa, sin contratar un equipo interno', 'Hero debe conservar su H1 principal.');
assertContains($content, 'Quiero revisar mi situación TI', 'Hero debe conservar CTA principal.');
assertContains($content, 'Ver cómo trabajamos', 'Hero debe conservar CTA secundario.');
assertContains($content, 'hero-support-note', 'Hero debe conservar hero-support-note.');
assertContains($content, 'assets/img/about/about-8.webp', 'Hero debe conservar la imagen principal optimizada.');

echo "[OK] Tarjeta flotante del Hero validada correctamente.\n";

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
