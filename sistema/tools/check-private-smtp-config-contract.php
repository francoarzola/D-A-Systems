<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta debe ejecutarse solo por CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$contactConfig = readFileOrFail($root . '/config/contact.php', 'config/contact.php');
$gitignore = readFileOrFail($root . '/.gitignore', '.gitignore');
$htaccess = readFileOrFail($root . '/.htaccess', '.htaccess');
$documentation = readFileOrFail($root . '/docs/sistema-interno/113-configuracion-smtp-privada.md', 'docs/sistema-interno/113-configuracion-smtp-privada.md');

assertContains($contactConfig, 'DA_SYSTEMS_PRIVATE_CONTACT_CONFIG', 'config/contact.php debe referenciar DA_SYSTEMS_PRIVATE_CONTACT_CONFIG.');
assertContains($contactConfig, 'private/dasystems/contact.php', 'config/contact.php debe referenciar private/dasystems/contact.php.');
assertContains($contactConfig, 'DA_SYSTEMS_SMTP_HOST', 'config/contact.php debe mantener fallback a DA_SYSTEMS_SMTP_HOST.');
assertContains($contactConfig, 'contacto@dasystems.cl', 'config/contact.php debe mantener contacto@dasystems.cl como fallback.');
assertContains($contactConfig, 'DA_SYSTEMS_RECEIVING_EMAIL', 'config/contact.php debe mantener fallback a DA_SYSTEMS_RECEIVING_EMAIL.');
assertContains($contactConfig, 'DA_SYSTEMS_AUTO_REPLY_ENABLED', 'config/contact.php debe mantener fallback a DA_SYSTEMS_AUTO_REPLY_ENABLED.');

if (strpos($gitignore, 'config/contact.local.php') === false && strpos($gitignore, 'config/*.private.php') === false) {
    fail('.gitignore debe bloquear config/contact.local.php o config/*.private.php.');
}
assertContains($gitignore, 'private/', '.gitignore debe bloquear private/.');
assertContains($htaccess, 'vendor', '.htaccess debe bloquear vendor.');

$combined = $contactConfig . "\n" . $gitignore . "\n" . $htaccess . "\n" . $documentation;
$withoutDocumentation = $contactConfig . "\n" . $gitignore . "\n" . $htaccess;
assertNotContains($combined, 'smtp.gmail.com', 'No debe existir smtp.gmail.com.');
assertNotContains($combined, 'dasystemstechnology@gmail.com', 'No debe existir dasystemstechnology@gmail.com.');
assertNotMatches($withoutDocumentation, '/(?<![a-z0-9])[a-z0-9]{4}\s+[a-z0-9]{4}\s+[a-z0-9]{4}\s+[a-z0-9]{4}(?![a-z0-9])/i', 'No debe existir password de aplicacion de Google hardcodeado.');
assertNotContains($withoutDocumentation, 'CLAVE_REAL_DEL_CORREO', 'El placeholder CLAVE_REAL_DEL_CORREO solo puede aparecer en documentacion.');
assertContains($documentation, 'CLAVE_REAL_DEL_CORREO', 'La documentacion debe usar CLAVE_REAL_DEL_CORREO como placeholder.');
assertContains($documentation, '/home/jjvxkghg/private/dasystems/contact.php', 'La documentacion debe indicar la ruta privada recomendada.');

$buildStatus = shell_exec('git status --short -- build 2>NUL');
if (is_string($buildStatus) && trim($buildStatus) !== '') {
    fail('No se debe modificar build/. Estado: ' . trim($buildStatus));
}

echo "[OK] Configuración SMTP privada validada correctamente.\n";

function readFileOrFail(string $path, string $label): string
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
