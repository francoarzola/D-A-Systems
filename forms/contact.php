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

include $php_email_form_path;

$method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
$remote_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$client_ip = filter_var($remote_ip, FILTER_VALIDATE_IP) ? $remote_ip : 'unknown';
$ip_hash = hash('sha256', $client_ip);
$user_agent_hash = isset($_SERVER['HTTP_USER_AGENT']) && trim((string)$_SERVER['HTTP_USER_AGENT']) !== ''
  ? hash('sha256', $_SERVER['HTTP_USER_AGENT'])
  : null;
$rate_limit_path = __DIR__ . '/../storage/rate-limit/contact-' . $ip_hash . '.json';
$log_path = __DIR__ . '/../storage/logs/contact.log';
$max_attempts = 5;
$window_seconds = 900;

function ensure_directory_exists(string $path): void {
  if (!is_dir($path)) {
    @mkdir($path, 0755, true);
  }
}

function write_json_line(string $path, array $payload): void {
  ensure_directory_exists(dirname($path));
  @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
}

function log_event(string $event, string $status, string $reason = ''): void {
  global $ip_hash, $user_agent_hash, $method, $log_path;

  $payload = [
    'timestamp' => gmdate('c'),
    'event' => $event,
    'ip_hash' => $ip_hash,
    'method' => $method,
    'status' => $status,
  ];

  if ($reason !== '') {
    $payload['reason'] = $reason;
  }

  if ($user_agent_hash !== null) {
    $payload['user_agent_hash'] = $user_agent_hash;
  }

  write_json_line($log_path, $payload);
}

function load_rate_limit(string $path): array {
  if (!file_exists($path)) {
    return [];
  }

  $content = @file_get_contents($path);
  if ($content === false) {
    return [];
  }

  $data = json_decode($content, true);
  if (!is_array($data) || !isset($data['attempts']) || !is_array($data['attempts'])) {
    return [];
  }

  return array_values(array_filter($data['attempts'], static function ($timestamp) {
    return is_int($timestamp) || ctype_digit((string)$timestamp);
  }));
}

function save_rate_limit(string $path, array $attempts): void {
  ensure_directory_exists(dirname($path));
  $payload = ['attempts' => array_values($attempts)];
  @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", LOCK_EX);
}

function prune_attempts(array $attempts, int $window_seconds): array {
  $threshold = time() - $window_seconds;
  return array_values(array_filter($attempts, static function ($timestamp) use ($threshold) {
    return ((is_int($timestamp) || ctype_digit((string)$timestamp)) && (int) $timestamp >= $threshold);
  }));
}

function add_rate_limit_attempt(string $path, array $attempts): array {
  $attempts[] = time();
  save_rate_limit($path, $attempts);
  return $attempts;
}

if ($method !== 'POST') {
  log_event('invalid_method', 'error', 'invalid_method');
  http_response_code(405);
  echo 'Método no permitido.';
  exit;
}

$attempts = prune_attempts(load_rate_limit($rate_limit_path), $window_seconds);
if (count($attempts) >= $max_attempts) {
  log_event('rate_limit_blocked', 'error', 'rate_limit_exceeded');
  http_response_code(429);
  echo 'Has realizado demasiados intentos. Intenta nuevamente más tarde.';
  exit;
}

$attempts = add_rate_limit_attempt($rate_limit_path, $attempts);
log_event('send_attempt', 'pending');

$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
  if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') {
    log_event('missing_required_fields', 'error', 'missing_required_fields');
    http_response_code(400);
    echo 'Faltan campos obligatorios.';
    exit;
  }
}

if (!isset($_POST['privacy_consent']) || trim((string)$_POST['privacy_consent']) !== 'accepted') {
  log_event('privacy_consent_missing', 'error', 'privacy_consent_missing');
  http_response_code(400);
  echo 'Debes aceptar la política de privacidad para enviar la solicitud.';
  exit;
}

$website = isset($_POST['website']) ? trim((string)$_POST['website']) : '';
if ($website !== '') {
  log_event('honeypot_triggered', 'blocked', 'honeypot_triggered');
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
  log_event('invalid_length', 'error', 'invalid_length');
  http_response_code(400);
  echo 'Uno o más campos exceden la longitud permitida.';
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  log_event('invalid_email', 'error', 'invalid_email');
  http_response_code(400);
  echo 'El correo electrónico no es válido.';
  exit;
}

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

$send_result = $contact->send();
if (trim($send_result) === 'OK') {
  log_event('send_completed', 'success', 'send_ok');
  echo 'OK';
} else {
  log_event('send_completed', 'error', 'send_failed');
  echo $send_result;
}
