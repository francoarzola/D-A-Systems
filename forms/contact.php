<?php
$config_path = __DIR__ . '/../config/contact.php';
if (!file_exists($config_path)) {
  http_response_code(500);
  echo 'No fue posible procesar la solicitud en este momento.';
  exit;
}

$config = include $config_path;
if (!is_array($config)) {
  http_response_code(500);
  echo 'No fue posible procesar la solicitud en este momento.';
  exit;
}

$receiving_email_address = $config['receiving_email_address'] ?? '';
if (!filter_var($receiving_email_address, FILTER_VALIDATE_EMAIL)) {
  http_response_code(500);
  echo 'No fue posible procesar la solicitud en este momento.';
  exit;
}

$php_email_form_path = __DIR__ . '/../assets/vendor/php-email-form/php-email-form.php';
if (!file_exists($php_email_form_path)) {
  http_response_code(500);
  echo 'No fue posible procesar la solicitud en este momento.';
  exit;
}

$rate_limit_dir = __DIR__ . '/../storage/rate-limit';
$logs_dir = __DIR__ . '/../storage/logs';

if (!is_dir($rate_limit_dir) && !mkdir($rate_limit_dir, 0755, true)) {
  http_response_code(500);
  echo 'No fue posible procesar la solicitud en este momento.';
  exit;
}

if (!is_dir($logs_dir) && !mkdir($logs_dir, 0755, true)) {
  http_response_code(500);
  echo 'No fue posible procesar la solicitud en este momento.';
  exit;
}

$get_client_ip = static function () {
  $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';

  if (filter_var($remote_addr, FILTER_VALIDATE_IP)) {
    return $remote_addr;
  }

  // X-Forwarded-For solo debe usarse con cuidado detrás de proxy confiable.
  return 'unknown';
};

$client_ip = $get_client_ip();
$ip_hash = hash('sha256', $client_ip);
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$user_agent_hash = $user_agent !== '' ? hash('sha256', $user_agent) : null;
$method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
$log_file = $logs_dir . '/contact.log';

$log_event = static function ($event, $status, $reason) use ($ip_hash, $user_agent_hash, $method, $log_file) {
  $entry = [
    'timestamp' => gmdate('c'),
    'event' => $event,
    'ip_hash' => $ip_hash,
    'method' => $method,
    'status' => $status,
    'reason' => $reason,
  ];

  if ($user_agent_hash !== null) {
    $entry['user_agent_hash'] = $user_agent_hash;
  }

  $encoded = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($encoded !== false) {
    @file_put_contents($log_file, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
  }
};

if ($method !== 'POST') {
  $log_event('invalid_method', 405, 'method_not_allowed');
  http_response_code(405);
  echo 'Método no permitido.';
  exit;
}

$window_seconds = 900;
$max_attempts = 5;
$now = time();
$rate_limit_file = $rate_limit_dir . '/contact-' . $ip_hash . '.json';

$attempts = [];
if (file_exists($rate_limit_file)) {
  $raw_attempts = file_get_contents($rate_limit_file);
  if (is_string($raw_attempts) && $raw_attempts !== '') {
    $decoded_attempts = json_decode($raw_attempts, true);
    if (is_array($decoded_attempts)) {
      foreach ($decoded_attempts as $attempt_timestamp) {
        if (is_int($attempt_timestamp) || ctype_digit((string)$attempt_timestamp)) {
          $attempts[] = (int)$attempt_timestamp;
        }
      }
    }
  }
}

$attempts = array_values(array_filter($attempts, static function ($attempt_timestamp) use ($now, $window_seconds) {
  return $attempt_timestamp > ($now - $window_seconds);
}));

if (count($attempts) >= $max_attempts) {
  $log_event('rate_limit_blocked', 429, 'too_many_attempts');
  http_response_code(429);
  echo 'Has realizado demasiados intentos. Intenta nuevamente más tarde.';
  exit;
}

$attempts[] = $now;
@file_put_contents($rate_limit_file, json_encode($attempts), LOCK_EX);

$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
  if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') {
    $log_event('missing_required_fields', 400, 'required_fields_missing');
    http_response_code(400);
    echo 'Faltan campos obligatorios.';
    exit;
  }
}

if (!isset($_POST['privacy_consent']) || trim((string)$_POST['privacy_consent']) !== 'accepted') {
  $log_event('privacy_consent_missing', 400, 'privacy_consent_not_accepted');
  http_response_code(400);
  echo 'Debes aceptar la política de privacidad para enviar la solicitud.';
  exit;
}

$website = isset($_POST['website']) ? trim((string)$_POST['website']) : '';
if ($website !== '') {
  $log_event('honeypot_triggered', 200, 'honeypot_not_empty');
  echo 'OK';
  exit;
}

$sanitize = static function ($value) {
  return trim(strip_tags((string)$value));
};

$strip_header_injection = static function ($value) {
  return str_replace(["\r", "\n", "%0a", "%0d"], '', $value);
};

$name = $strip_header_injection($sanitize($_POST['name']));
$email = $strip_header_injection($sanitize($_POST['email']));
$phone = isset($_POST['phone']) ? $sanitize($_POST['phone']) : '';
$company = isset($_POST['company']) ? $sanitize($_POST['company']) : '';
$subject = $strip_header_injection($sanitize($_POST['subject']));
$message = $sanitize($_POST['message']);

if (
  mb_strlen($name) > 100 ||
  mb_strlen($email) > 150 ||
  mb_strlen($phone) > 30 ||
  mb_strlen($company) > 150 ||
  mb_strlen($subject) > 150 ||
  mb_strlen($message) > 3000
) {
  $log_event('invalid_length', 400, 'field_length_exceeded');
  http_response_code(400);
  echo 'Uno o más campos exceden la longitud permitida.';
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $log_event('invalid_email', 400, 'invalid_sender_email');
  http_response_code(400);
  echo 'El correo electrónico no es válido.';
  exit;
}

include $php_email_form_path;

$contact = new PHP_Email_Form();
$contact->ajax = true;
$contact->to = $receiving_email_address;
$contact->from_name = $name;
$contact->from_email = $email;
$contact->subject = $subject;

$contact->add_message($name, 'Nombre');
$contact->add_message($email, 'Email');
if ($phone !== '') {
  $contact->add_message($phone, 'Teléfono');
}
if ($company !== '') {
  $contact->add_message($company, 'Empresa');
}
$contact->add_message($subject, 'Asunto');
$contact->add_message($message, 'Mensaje', 10);

$log_event('send_attempt', 200, 'sending_contact_message');
$send_result = $contact->send();
$log_event('send_completed', 200, 'contact_send_finished');

echo $send_result;
