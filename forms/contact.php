<?php
/**
 * Contact form handler for D&A Systems.
 */

$receiving_email_address = 'dasystemstechnology@gmail.com';

if (!file_exists('../assets/vendor/php-email-form/php-email-form.php')) {
  http_response_code(500);
  echo 'No fue posible procesar la solicitud en este momento.';
  exit;
}

include '../assets/vendor/php-email-form/php-email-form.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Método no permitido.';
  exit;
}

$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
  if (!isset($_POST[$field]) || trim((string) $_POST[$field]) === '') {
    http_response_code(400);
    echo 'Faltan campos obligatorios.';
    exit;
  }
}

// Honeypot anti-spam: success silencioso si viene lleno
$website = isset($_POST['website']) ? trim((string) $_POST['website']) : '';
if ($website !== '') {
  echo 'OK';
  exit;
}

$sanitize = static function ($value) {
  return trim(strip_tags((string) $value));
};

$strip_header_injection = static function ($value) {
  return str_replace(["\r", "\n", "%0a", "%0d"], '', $value);
};

$name = $sanitize($_POST['name']);
$email = $sanitize($_POST['email']);
$phone = isset($_POST['phone']) ? $sanitize($_POST['phone']) : '';
$company = isset($_POST['company']) ? $sanitize($_POST['company']) : '';
$subject = $sanitize($_POST['subject']);
$message = $sanitize($_POST['message']);

$name = $strip_header_injection($name);
$email = $strip_header_injection($email);
$subject = $strip_header_injection($subject);

if (mb_strlen($name) > 100 || mb_strlen($email) > 150 || mb_strlen($phone) > 30 || mb_strlen($company) > 150 || mb_strlen($subject) > 150 || mb_strlen($message) > 3000) {
  http_response_code(400);
  echo 'Uno o más campos exceden la longitud permitida.';
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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

echo $contact->send();
