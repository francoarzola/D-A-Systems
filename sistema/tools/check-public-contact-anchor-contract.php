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

$index = file_get_contents($indexPath);

if ($index === false) {
    fail('No fue posible leer index.html.');
}

assertContains($index, '<section id="contact" class="contact section">', 'Debe existir la seccion #contact.');
assertContains($index, 'Contacto', 'Debe existir el texto Contacto.');
assertContains($index, 'class="php-email-form contact-form"', 'Debe existir el formulario de contacto.');
assertContains($index, 'action="forms/contact.php"', 'El formulario debe enviar a forms/contact.php.');

foreach (['name="name"', 'name="email"', 'name="phone"', 'name="company"', 'name="subject"', 'name="message"'] as $field) {
    assertContains($index, $field, 'Debe existir el campo ' . $field . '.');
}

assertContains($index, 'csrf_token', 'Debe existir csrf_token.');
assertContains($index, 'name="website"', 'Debe existir honeypot website.');
assertContains($index, 'privacy_consent', 'Debe existir privacy_consent.');
assertContains($index, 'href="#contact"', 'Debe existir enlace interno href="#contact".');

$contactSection = extractContactSection($index);

assertContains($contactSection, '<div class="container">', 'El contenedor funcional de contacto no debe depender de AOS.');
assertNotContains($contactSection, '<div class="container" data-aos="fade-up" data-aos-delay="100">', 'El contenedor funcional de contacto no debe tener data-aos fade-up.');
assertContains($contactSection, '<div class="col-lg-7 order-lg-1 order-2">', 'La columna del formulario no debe depender de AOS.');
assertContains($contactSection, '<div class="col-lg-5 order-lg-2 order-1">', 'La columna lateral no debe depender de AOS.');
assertNotContains($contactSection, '<div class="col-lg-7 order-lg-1 order-2" data-aos="fade-right"', 'La columna del formulario no debe tener data-aos fade-right.');
assertNotContains($contactSection, '<div class="col-lg-5 order-lg-2 order-1" data-aos="fade-left"', 'La columna lateral no debe tener data-aos fade-left.');

foreach (['nosotros.html', 'servicios-ti.html', 'soluciones-ti.html', 'politica-privacidad.html', 'terminos-condiciones.html'] as $page) {
    $path = $root . '/' . $page;

    if (!is_file($path)) {
        fail('No existe ' . $page . '.');
    }

    $content = file_get_contents($path);

    if ($content === false) {
        fail('No fue posible leer ' . $page . '.');
    }

    assertContains($content, 'href="index.html#contact"', $page . ' debe conservar enlace a index.html#contact.');
}

$publicPages = [
    'index.html' => $index,
];

foreach (['nosotros.html', 'servicios-ti.html', 'soluciones-ti.html', 'politica-privacidad.html', 'terminos-condiciones.html'] as $page) {
    $content = file_get_contents($root . '/' . $page);

    if ($content === false) {
        fail('No fue posible leer ' . $page . '.');
    }

    $publicPages[$page] = $content;
}

$allPublicHtml = implode("\n", $publicPages);

assertNotContains($allPublicHtml, 'D&amp;A Systems SpA', 'No debe aparecer D&amp;A Systems SpA.');
assertNotContains(html_entity_decode($allPublicHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'D&A Systems SpA', 'No debe aparecer D&A Systems SpA.');
assertNotContains($allPublicHtml, 'dasystemstechnology@gmail.com', 'No debe aparecer dasystemstechnology@gmail.com.');
assertContains($allPublicHtml, 'contacto@dasystems.cl', 'Debe aparecer contacto@dasystems.cl.');

foreach (['Ãƒ', 'Ã‚', 'Ã¢â‚¬', 'Ã¢â€“', 'Ã¢â€”', 'Ã¢â‚¬â„¢', 'Ã¢â‚¬Å“', 'ï¿½', '�'] as $mojibake) {
    assertNotContains($allPublicHtml, $mojibake, 'No debe haber mojibake: ' . $mojibake);
}

echo "[OK] Navegación hacia Contacto validada correctamente.\n";

function extractContactSection(string $html): string
{
    $start = strpos($html, '<section id="contact" class="contact section">');

    if ($start === false) {
        fail('No fue posible ubicar la seccion Contacto.');
    }

    $end = strpos($html, '</section>', $start);

    if ($end === false) {
        fail('No fue posible ubicar el cierre de la seccion Contacto.');
    }

    return substr($html, $start, $end - $start);
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
