<?php
$receivingEmail = getenv('DA_SYSTEMS_RECEIVING_EMAIL') ?: 'dasystemstechnology@gmail.com';
$smtpHost = getenv('DA_SYSTEMS_SMTP_HOST');
$smtp = null;

if ($smtpHost !== false && trim((string) $smtpHost) !== '') {
    $smtpPort = getenv('DA_SYSTEMS_SMTP_PORT');
    $smtpEncryption = getenv('DA_SYSTEMS_SMTP_ENCRYPTION') ?: 'tls';
    $smtpUsername = getenv('DA_SYSTEMS_SMTP_USERNAME') ?: '';
    $smtpPassword = getenv('DA_SYSTEMS_SMTP_PASSWORD') ?: '';
    $smtpMailer = getenv('DA_SYSTEMS_SMTP_MAILER') ?: $smtpUsername;

    $smtp = [
        'host' => trim((string) $smtpHost),
        'username' => trim((string) $smtpUsername),
        'password' => trim((string) $smtpPassword),
        'port' => is_numeric($smtpPort) ? (int) $smtpPort : 587,
        'encryption' => trim((string) $smtpEncryption),
        'mailer' => trim((string) $smtpMailer),
    ];
}

$autoReplyEnabled = filter_var(
    getenv('DA_SYSTEMS_AUTO_REPLY_ENABLED'),
    FILTER_VALIDATE_BOOLEAN,
    FILTER_NULL_ON_FAILURE
);

return [
  'receiving_email_address' => trim((string) $receivingEmail),
  'smtp' => $smtp,
  'auto_reply_enabled' => $autoReplyEnabled === null ? false : $autoReplyEnabled,
];
