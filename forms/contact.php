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

function start_da_systems_session(): void {
  if (session_status() !== PHP_SESSION_NONE) {
    return;
  }

  $secure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
  $cookieParams = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['SERVER_NAME'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
  ];

  session_name('DA_SYSTEMS_SESSION');
  if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params($cookieParams);
  } else {
    session_set_cookie_params(
      $cookieParams['lifetime'],
      $cookieParams['path'],
      $cookieParams['domain'],
      $cookieParams['secure'],
      $cookieParams['httponly']
    );
  }

  session_start();
}

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

start_da_systems_session();

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
  log_event('csrf_token_invalid', 'error', 'csrf_token_invalid');
  http_response_code(400);
  echo 'No fue posible validar la solicitud. Recarga la página e inténtalo nuevamente.';
  exit;
}

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

$allowed_subjects = [
  'Soporte técnico',
  'Infraestructura y redes',
  'Respaldos y continuidad operativa',
  'Seguridad y mantenimiento TI',
  'Administración tecnológica',
  'Inventario y activos TI',
  'Otro requerimiento TI',
];

if (!in_array($subject, $allowed_subjects, true)) {
  log_event('invalid_subject', 'error', 'invalid_subject');
  http_response_code(400);
  echo 'El requerimiento seleccionado no es válido.';
  exit;
}

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

if (isset($config['smtp']) && is_array($config['smtp'])) {
  $contact->smtp = $config['smtp'];
}

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

  $autoReplyEnabled = filter_var($config['auto_reply_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
  if ($autoReplyEnabled && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $autoReply = new PHP_Email_Form();
    $autoReply->ajax = true;
    $autoReply->to = $email;
    $autoReply->from_name = 'D&A Systems';
    $autoReply->from_email = $receiving_email_address;
    $autoReply->subject = 'Hemos recibido tu solicitud | D&A Systems';
    $autoReply->content_type = 'text/html';

    if (isset($config['smtp']) && is_array($config['smtp'])) {
      $autoReply->smtp = $config['smtp'];
    }

    $autoReply->message = '<div style="background:#F5F7FA;padding:24px;font-family:Arial,Helvetica,sans-serif;color:#00143B;line-height:1.5;">'
      . '<div style="max-width:600px;margin:0 auto;background:#ffffff;padding:24px;border-radius:16px;border:1px solid #d9e2ec;">'
      . '<h1 style="margin-top:0;font-size:24px;color:#00143B;">Hemos recibido tu solicitud</h1>'
      . '<p>Hola,</p>'
      . '<p>Gracias por contactar a D&A Systems. Hemos recibido tu solicitud correctamente y la estamos revisando.</p>'
      . '<p>Te responderemos a la brevedad con la información y pasos siguientes. Si quieres agregar algún detalle adicional, responde a este mensaje.</p>'
      . '<p style="margin-bottom:0;">Saludos,<br>D&A Systems</p>'
      . '<hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">'
      . '<p style="font-size:14px;color:#64748b;margin:0;">Si necesitas ayuda urgente, también puedes escribirnos por WhatsApp al <strong>+56 9 7300 0457</strong>.</p>'
      . '</div></div>';

    $autoReply->option('AltBody', "Hola,\n\nGracias por contactar a D&A Systems. Hemos recibido tu solicitud correctamente y la estamos revisando.\n\nTe responderemos a la brevedad con la información y pasos siguientes. Si quieres agregar algún detalle adicional, responde a este mensaje.\n\nSaludos,\nD&A Systems\n\nSi necesitas ayuda urgente, también puedes escribirnos por WhatsApp al +56 9 7300 0457.");

    $autoreplyResult = trim($autoReply->send());
    if ($autoreplyResult === 'OK') {
      log_event('autoreply_completed', 'success', 'autoreply_ok');
    } else {
      log_event('autoreply_failed', 'warning', substr($autoreplyResult, 0, 255));
    }
  }

  echo 'OK';
} else {
  log_event('send_completed', 'error', substr($send_result, 0, 255));
  echo 'No fue posible enviar el mensaje en este momento. Intenta nuevamente más tarde o escríbenos por WhatsApp.';
}
