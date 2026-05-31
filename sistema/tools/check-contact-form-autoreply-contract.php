<?php

$root = dirname(__DIR__, 2);
$configPath = $root . '/config/contact.php';
$contactPath = $root . '/forms/contact.php';

$errors = [];

if (!file_exists($configPath)) {
    $errors[] = 'No se encontró config/contact.php';
}

if (!file_exists($contactPath)) {
    $errors[] = 'No se encontró forms/contact.php';
}

if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error . "\n");
    }
    exit(1);
}

$config = include $configPath;
if (!is_array($config)) {
    fwrite(STDERR, 'config/contact.php no devuelve un array' . "\n");
    exit(1);
}

if (!array_key_exists('auto_reply_enabled', $config)) {
    fwrite(STDERR, 'Falta la clave auto_reply_enabled en config/contact.php' . "\n");
}

if (!array_key_exists('receiving_email_address', $config)) {
    fwrite(STDERR, 'Falta la clave receiving_email_address en config/contact.php' . "\n");
}

$contactContents = file_get_contents($contactPath);
if ($contactContents === false) {
    fwrite(STDERR, 'No se pudo leer forms/contact.php' . "\n");
    exit(1);
}

$configContents = file_get_contents($configPath);
if ($configContents === false) {
    fwrite(STDERR, 'No se pudo leer config/contact.php' . "\n");
    exit(1);
}

$checks = [
    'csrf_token' => 'Debe validar csrf_token',
    'privacy_consent' => 'Debe validar privacy_consent',
    'honeypot_triggered' => 'Debe bloquear el honeypot',
    'rate_limit_blocked' => 'Debe implementar limitación de intentos',
    "new PHP_Email_Form()" => 'Debe utilizar PHP_Email_Form para el envío',
    "Hemos recibido tu solicitud | D&A Systems" => 'Debe usar el asunto de autorespuesta correcto',
    "from_name = 'D&A Systems'" => 'La autorespuesta debe usar D&A Systems como remitente visible',
    'from_email = $receiving_email_address' => 'La autorespuesta no debe usar el correo del usuario como remitente principal',
    'log_event(\'autoreply_failed\'' => 'Debe registrar fallos de la autorespuesta',
    'log_event(\'send_completed\'' => 'Debe registrar el envío principal',
];

foreach ($checks as $needle => $message) {
    if (strpos($contactContents, $needle) === false) {
        fwrite(STDERR, $message . "\n");
        $errors[] = $message;
    }
}

if (strpos($configContents, 'DA_SYSTEMS_AUTO_REPLY_ENABLED') === false) {
    fwrite(STDERR, 'El archivo config/contact.php debe exponer DA_SYSTEMS_AUTO_REPLY_ENABLED' . "\n");
    $errors[] = 'Falta la variable de entorno DA_SYSTEMS_AUTO_REPLY_ENABLED en config/contact.php';
}

if (strpos($configContents, 'auto_reply_enabled') === false) {
    fwrite(STDERR, 'El archivo config/contact.php debe contener la clave auto_reply_enabled' . "\n");
    $errors[] = 'Falta la cadena auto_reply_enabled en config/contact.php';
}

if (count($errors) > 0) {
    exit(1);
}

fwrite(STDOUT, '[OK] Autorespuesta del formulario de contacto validada correctamente.' . "\n");
exit(0);
