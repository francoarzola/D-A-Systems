<?php

$config = [
    'receiving_email_address' => 'contacto@dasystems.cl',
    'smtp' => null,
    'auto_reply_enabled' => false,
];

$privateConfigPath = getenv('DA_SYSTEMS_PRIVATE_CONTACT_CONFIG');
if ($privateConfigPath === false || trim((string) $privateConfigPath) === '') {
    $privateConfigPath = dirname(__DIR__, 2) . '/private/dasystems/contact.php';
}

if (is_string($privateConfigPath) && is_file($privateConfigPath)) {
    $privateConfig = require $privateConfigPath;

    if (is_array($privateConfig)) {
        if (isset($privateConfig['receiving_email_address']) && is_string($privateConfig['receiving_email_address'])) {
            $receivingEmail = trim($privateConfig['receiving_email_address']);
            if ($receivingEmail !== '') {
                $config['receiving_email_address'] = $receivingEmail;
            }
        }

        if (isset($privateConfig['auto_reply_enabled'])) {
            $config['auto_reply_enabled'] = filter_var($privateConfig['auto_reply_enabled'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($privateConfig['smtp']) && is_array($privateConfig['smtp'])) {
            $smtpConfig = $privateConfig['smtp'];
            $requiredSmtpKeys = ['host', 'username', 'password', 'port', 'encryption', 'mailer'];
            $hasRequiredSmtpKeys = true;

            foreach ($requiredSmtpKeys as $key) {
                if (!array_key_exists($key, $smtpConfig)) {
                    $hasRequiredSmtpKeys = false;
                    break;
                }
            }

            if ($hasRequiredSmtpKeys) {
                $config['smtp'] = [
                    'host' => trim((string) $smtpConfig['host']),
                    'username' => trim((string) $smtpConfig['username']),
                    'password' => (string) $smtpConfig['password'],
                    'port' => is_numeric($smtpConfig['port']) ? (int) $smtpConfig['port'] : 465,
                    'encryption' => trim((string) $smtpConfig['encryption']),
                    'mailer' => trim((string) $smtpConfig['mailer']),
                ];
            }
        }
    }
}

if ($config['smtp'] === null) {
    $receivingEmail = getenv('DA_SYSTEMS_RECEIVING_EMAIL') ?: $config['receiving_email_address'];
    $config['receiving_email_address'] = trim((string) $receivingEmail);
    $autoReplyEnabled = filter_var(
        getenv('DA_SYSTEMS_AUTO_REPLY_ENABLED'),
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE
    );
    $config['auto_reply_enabled'] = $autoReplyEnabled === null ? false : $autoReplyEnabled;

    $smtpHost = getenv('DA_SYSTEMS_SMTP_HOST');

    if ($smtpHost === false || trim((string) $smtpHost) === '') {
        return $config;
    }

    $smtpPort = getenv('DA_SYSTEMS_SMTP_PORT');
    $smtpEncryption = getenv('DA_SYSTEMS_SMTP_ENCRYPTION') ?: 'tls';
    $smtpUsername = getenv('DA_SYSTEMS_SMTP_USERNAME') ?: '';
    $smtpPassword = getenv('DA_SYSTEMS_SMTP_PASSWORD') ?: '';
    $smtpMailer = getenv('DA_SYSTEMS_SMTP_MAILER') ?: $smtpUsername;

    $config['smtp'] = [
        'host' => trim((string) $smtpHost),
        'username' => trim((string) $smtpUsername),
        'password' => trim((string) $smtpPassword),
        'port' => is_numeric($smtpPort) ? (int) $smtpPort : 587,
        'encryption' => trim((string) $smtpEncryption),
        'mailer' => trim((string) $smtpMailer),
    ];

}

return $config;
