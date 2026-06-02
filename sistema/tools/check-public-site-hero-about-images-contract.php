<?php

$root = dirname(__DIR__, 2);
$indexFile = $root . DIRECTORY_SEPARATOR . 'index.html';
$aboutDir = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'about';

if (! file_exists($indexFile)) {
    fwrite(STDERR, "ERROR: index.html no encontrado.\n");
    exit(1);
}

$index = file_get_contents($indexFile);
$patterns = [
    'hero' => '/<img\s+[^>]*src="([^"]*assets\/img\/about\/about-8\.webp)"[^>]*alt="([^"]*)"/i',
    'about' => '/<img\s+[^>]*src="([^"]*assets\/img\/about\/about-square-8\.webp)"[^>]*alt="([^"]*)"/i',
];

$requiredFiles = [];

foreach ($patterns as $section => $pattern) {
    if (! preg_match($pattern, $index, $matches)) {
        fwrite(STDERR, "ERROR: No se encontró la imagen esperada para '{$section}' en index.html.\n");
        exit(1);
    }

    $src = $matches[1];
    $alt = $matches[2];

    if (preg_match('/\s/', $src)) {
        fwrite(STDERR, "ERROR: La ruta de la imagen '{$src}' contiene espacios.\n");
        exit(1);
    }

    if (strpos($src, 'assets/img/about/') !== 0) {
        fwrite(STDERR, "ERROR: La ruta de la imagen '{$src}' no está dentro de assets/img/about/.\n");
        exit(1);
    }

    $requiredFiles[] = $aboutDir . DIRECTORY_SEPARATOR . basename($src);
    echo "OK: Encontrada imagen '{$section}' en index.html con alt='{$alt}'.\n";
}

foreach ($requiredFiles as $file) {
    if (! file_exists($file)) {
        fwrite(STDERR, "ERROR: El archivo de imagen esperado no existe: {$file}\n");
        exit(1);
    }

    $real = realpath($file);
    if (strpos($real, realpath($aboutDir)) !== 0) {
        fwrite(STDERR, "ERROR: El archivo de imagen '{$file}' está fuera de assets/img/about/.\n");
        exit(1);
    }

    echo "OK: Archivo existente {$file}.\n";
}

$contactForm = '/<form[^>]+action="forms\/contact\.php"[^>]*>/i';
$privacyConsent = '/<input[^>]+name="privacy_consent"[^>]*>/i';
$csrfField = '/<input[^>]+name="csrf_token"[^>]*>/i';
$honeypot = '/<input[^>]+name="website"[^>]*>/i';

foreach (['contactForm' => $contactForm, 'privacyConsent' => $privacyConsent, 'csrfField' => $csrfField, 'honeypot' => $honeypot] as $name => $pattern) {
    if (! preg_match($pattern, $index)) {
        fwrite(STDERR, "ERROR: No se encontró el elemento {$name} en index.html.\n");
        exit(1);
    }
}

echo "OK: Formulario de contacto y campos anti-spam están presentes.\n";

exit(0);
