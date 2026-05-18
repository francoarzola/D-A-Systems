<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/CsrfService.php';

use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Core\CsrfService;

$session = new SessionManager();
$csrf = new CsrfService($session);

$session->start();

$message = '';
$isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

if ($isPost) {
    $token = $_POST['csrf_token'] ?? null;
    $valid = $csrf->validate(is_string($token) ? $token : null);

    if (!$valid) {
        $message = 'Error: la petición no pudo ser verificada. Intente nuevamente.';
    } else {
        $message = 'Flujo de login preparado. Validación de usuarios pendiente de base de datos.';
    }
}

$token = $csrf->token();

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Sistema interno D&A Systems</title>
  <link rel="stylesheet" href="assets/css/internal.css">
</head>
<body>
  <div class="container">
    <section class="header">
      <div class="brand">
        <span class="brand-dot"></span>
        D&A Systems
      </div>
      <h1 class="title">Acceso al sistema interno</h1>
      <p class="subtitle">Autenticación real pendiente de conexión a base de datos.</p>
    </section>

    <?php if ($message !== ''): ?>
      <div class="status-panel" role="status">
        <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
    <?php endif; ?>

    <form method="post" action="login.php" class="card" style="margin-top:20px;">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required placeholder="usuario@empresa.cl" />

      <label for="password" style="margin-top:12px;">Contraseña</label>
      <input id="password" name="password" type="password" required placeholder="Contraseña" />

      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />

      <div class="button-wrapper" style="margin-top:18px;">
        <button class="button-disabled" type="submit">Acceder</button>
      </div>
    </form>

    <footer>
      <small>Interfaz de acceso temporal. Acceso solo para personal autorizado.</small>
    </footer>
  </div>
</body>
</html>
