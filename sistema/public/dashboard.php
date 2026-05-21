<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/AuthGuard.php';

use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Core\AuthGuard;

$session = new SessionManager();
$session->start();

$guard = new AuthGuard();
$guard->requireAuth('login.php');

$authUserName = $guard->userName() ?: 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Sistema interno D&A Systems</title>
  <link rel="stylesheet" href="assets/css/internal.css">
</head>
<body>
  <div class="container">
    <section class="header">
      <div class="brand">
        <span class="brand-dot"></span>
        D&A Systems
      </div>
      <h1 class="title">Dashboard interno</h1>
      <p class="subtitle">Bienvenido, <?php echo htmlspecialchars($authUserName, ENT_QUOTES, 'UTF-8'); ?>.</p>
    </section>

    <section class="grid">
      <article class="card">
        <h2>Cotizaciones</h2>
        <p>Resumen de cotizaciones y accesos al módulo prioritario.</p>
      </article>
      <article class="card">
        <h2>Clientes</h2>
        <p>Acceso al listado y gestión de clientes.</p>
      </article>
      <article class="card">
        <h2>Atenciones</h2>
        <p>Registro y seguimiento de atenciones técnicas.</p>
      </article>
    </section>

    <div class="button-wrapper" style="margin-top:22px;">
      <a href="logout.php" class="button-disabled" role="button">Cerrar sesión</a>
    </div>

    <footer>
      <small>Panel de control interno protegido.</small>
    </footer>
  </div>
</body>
</html>
