<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

$root = dirname(__DIR__, 2);
$configPath = $root . '/config/contact.php';
$formPath = $root . '/forms/contact.php';

$requiredFiles = [
    $configPath,
    $formPath,
    $root . '/index.html',
    $root . '/assets/vendor/php-email-form/validate.js',
    $root . '/forms/csrf-token.php',
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        echo "[FAIL] Required file missing: {$file}\n";
        exit(1);
    }
}

$configContent = file_get_contents($configPath);
$formContent = file_get_contents($formPath);

$requiredConfig = [
    'DA_SYSTEMS_RECEIVING_EMAIL',
    'DA_SYSTEMS_SMTP_HOST',
    'DA_SYSTEMS_SMTP_PORT',
    'DA_SYSTEMS_SMTP_ENCRYPTION',
    'DA_SYSTEMS_SMTP_USERNAME',
    'DA_SYSTEMS_SMTP_PASSWORD',
    'DA_SYSTEMS_SMTP_MAILER',
    'receiving_email_address',
    'smtp',
];

$requiredForm = [
    'PHP_Email_Form',
    '$contact->smtp',
    'privacy_consent',
    'csrf_token',
    'honeypot',
    'website',
    'rate-limit',
    'contact.log',
    '$contact->send()',
];

$forbidden = [
    'eval(',
    'base64_decode(',
    'shell_exec(',
    'exec(',
    'system(',
];

$errors = [];

foreach ($requiredConfig as $needle) {
    if (stripos($configContent, $needle) === false) {
        $errors[] = "config/contact.php missing required token: {$needle}";
    }
}

foreach ($requiredForm as $needle) {
    if (stripos($formContent, $needle) === false) {
        $errors[] = "forms/contact.php missing required token: {$needle}";
    }
}

foreach ($forbidden as $needle) {
    if (stripos($configContent, $needle) !== false) {
        $errors[] = "config/contact.php contains forbidden pattern: {$needle}";
    }
    if (stripos($formContent, $needle) !== false) {
        $errors[] = "forms/contact.php contains forbidden pattern: {$needle}";
    }
}

if (!empty($errors)) {
    echo "[FAIL] Formulario de contacto no cumple el contrato:\n";
    foreach ($errors as $error) {
        echo " - {$error}\n";
    }
    exit(1);
}

echo "[OK] Formulario de contacto preparado para prueba SMTP local cumple el contrato esperado.\n";
exit(0);
