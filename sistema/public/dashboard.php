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

$pageTitle = 'Dashboard — Sistema interno D&A Systems';
$pageHeading = 'Dashboard interno';
$userName = $authUserName;

ob_start();
?>
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
<?php
$content = ob_get_clean();

require __DIR__ . '/../app/Views/layouts/internal.php';
