<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);

$htaccess = readRequiredFile($root . '/.htaccess', '.htaccess');
$contact = readRequiredFile($root . '/forms/contact.php', 'forms/contact.php');
$contactConfig = readRequiredFile($root . '/config/contact.php', 'config/contact.php');
$robots = readRequiredFile($root . '/robots.txt', 'robots.txt');

assertContains($htaccess, 'Options -Indexes', '.htaccess debe contener Options -Indexes.');
assertContains($htaccess, 'DirectoryIndex index.html index.php', '.htaccess debe contener DirectoryIndex index.html index.php.');
assertContains($htaccess, 'X-Content-Type-Options', '.htaccess debe mantener X-Content-Type-Options.');
assertContains($htaccess, 'Referrer-Policy', '.htaccess debe mantener Referrer-Policy.');
assertContains($htaccess, 'X-Frame-Options', '.htaccess debe mantener X-Frame-Options.');
assertContains($htaccess, 'Permissions-Policy', '.htaccess debe mantener Permissions-Policy.');
assertContains($htaccess, '.env', '.htaccess debe bloquear .env.');
assertContains($htaccess, 'composer\.json', '.htaccess debe bloquear composer.json.');
assertContains($htaccess, 'composer\.lock', '.htaccess debe bloquear composer.lock.');

foreach (['log', 'bak', 'backup', 'old', 'sql', 'zip', 'rar', 'tar', 'gz'] as $extension) {
    assertContains($htaccess, $extension, '.htaccess debe bloquear extension sensible: ' . $extension . '.');
}

foreach (['docs', 'storage', 'sistema/tools', 'sistema/config', 'config'] as $path) {
    assertContains($htaccess, $path, '.htaccess debe bloquear ' . $path . '.');
}

assertNotMatches($htaccess, '/^\s*Header\s+always\s+set\s+Strict-Transport-Security\b/im', '.htaccess no debe activar HSTS todavia.');
assertNotContains($htaccess, 'Content-Security-Policy', '.htaccess no debe activar CSP estricta todavia.');

foreach ([
    'Soporte técnico',
    'Infraestructura y redes',
    'Respaldos y continuidad operativa',
    'Seguridad y mantenimiento TI',
    'Administración tecnológica',
    'Inventario y activos TI',
    'Otro requerimiento TI',
] as $subject) {
    assertContains($contact, $subject, 'forms/contact.php debe contener subject permitido: ' . $subject . '.');
}

assertContains($contact, '$allowed_subjects', 'forms/contact.php debe contener whitelist de subjects.');
assertContains($contact, 'in_array($subject, $allowed_subjects, true)', 'forms/contact.php debe validar subject contra whitelist.');
assertContains($contact, 'invalid_subject', 'forms/contact.php debe registrar invalid_subject.');
assertNotContains($contact, 'echo $send_result;', 'forms/contact.php no debe exponer send_result al usuario.');
assertContains($contact, 'csrf_token', 'forms/contact.php debe mantener csrf_token.');
assertContains($contact, 'privacy_consent', 'forms/contact.php debe mantener privacy_consent.');
assertContains($contact, 'website', 'forms/contact.php debe mantener honeypot website.');
assertContains($contact, 'rate_limit', 'forms/contact.php debe mantener rate_limit.');
assertContains($contact, 'strip_header_injection', 'forms/contact.php debe mantener strip_header_injection.');

assertContains($contactConfig, 'contacto@dasystems.cl', 'config/contact.php debe seguir usando contacto@dasystems.cl.');
foreach (['DA_SYSTEMS_SMTP_HOST', 'DA_SYSTEMS_SMTP_USERNAME', 'DA_SYSTEMS_SMTP_PASSWORD', 'DA_SYSTEMS_SMTP_PORT'] as $envName) {
    assertContains($contactConfig, $envName, 'config/contact.php debe seguir usando variable ' . $envName . '.');
}

foreach (['/config/', '/storage/', '/docs/', '/sistema/tools/', '/sistema/config/'] as $disallow) {
    assertContains($robots, 'Disallow: ' . $disallow, 'robots.txt debe contener Disallow: ' . $disallow);
}

$combined = $htaccess . "\n" . $contact . "\n" . $contactConfig . "\n" . $robots;
assertNotContains($combined, 'dasystemstechnology@gmail.com', 'No debe aparecer dasystemstechnology@gmail.com.');

foreach (['Ãƒ', 'Ã‚', 'Ã¢â‚¬', 'Ã¢â€“', 'Ã¢â€”', 'Ã¢â‚¬â„¢', 'Ã¢â‚¬Å“', 'ï¿½', '�'] as $mojibake) {
    assertNotContains($combined, $mojibake, 'No debe haber mojibake: ' . $mojibake);
}

$build = $root . '/build/cpanel-production';
if (is_dir($build)) {
    foreach (['docs', 'sistema/tools'] as $forbiddenDirectory) {
        if (is_dir($build . '/' . $forbiddenDirectory)) {
            fail('build/cpanel-production no debe contener ' . $forbiddenDirectory . '.');
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($build, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($build) + 1));
        if (preg_match('/(^|\/)\.env(?:\..*)?$/i', $relative) === 1) {
            fail('build/cpanel-production no debe contener .env: ' . $relative);
        }
        if (preg_match('/\.(?:log|bak|sql|zip|rar|tar|gz)$/i', $relative) === 1) {
            fail('build/cpanel-production no debe contener archivo sensible: ' . $relative);
        }
    }
}

echo "[OK] Hardening de seguridad del sitio público validado correctamente.\n";

function readRequiredFile(string $path, string $label): string
{
    if (!is_file($path)) {
        fail('No existe ' . $label . '.');
    }

    $content = file_get_contents($path);
    if ($content === false) {
        fail('No fue posible leer ' . $label . '.');
    }

    return $content;
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

function assertNotMatches(string $content, string $pattern, string $message): void
{
    if (preg_match($pattern, $content) === 1) {
        fail($message);
    }
}

function fail(string $message): void
{
    echo '[ERROR] ' . $message . "\n";
    exit(1);
}
